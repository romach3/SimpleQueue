<?php namespace Kernel\Commands;

use Kernel\Helpers;
use Kernel\Queue\Thread;
use Kernel\Queue\Config;
use Kernel\Queue\Pheanstalk;
use Symfony\Component\Process\Process;

class Listen {
    protected $pheanstalk;

    public function __construct() {
        $this->pheanstalk = Pheanstalk::get();
    }

    public function __invoke($tube = null) {
        $thread = new Thread();
        $listen = [];
        if (null === $tube) {
            $tubes = Config::get('tubes', []);
            foreach($tubes as $tube) {
                $listen = array_merge($listen, $thread->getListenTubes($tube));
            }
        } else {
            $listen = $thread->getListenTubes($tube);
        }
        $processes = [];
        while(true) {
            foreach($listen as $tube) {
                if (isset($processes[$tube]) && count($processes[$tube]) > 0) {
                    usleep(Config::get('pause', 200));
                    continue;
                }
                $job = $this->pheanstalk
                    ->watch($tube)
                    ->ignore('default')
                    ->reserve(1);
                if ($job === false) {
                    continue;
                }
                $data = $job->getData();
                $this->pheanstalk->delete($job);
                $task = unserialize($data);
                if (isset($task['class']) && isset($task['data']) && Helpers::jobExists($task['class'])) {
                    $processes[$tube][$task['class']] = $this->start($tube, $task['class'], $task['data']);
                } else {
                    echo 'ERROR: ' . $task['class'] . PHP_EOL;
                }
                usleep(Config::get('pause', 200));
            }
            $this->check($processes);
        }
    }

    protected function start($tube, $class, $data) {
        $filename = $this->getFileName();
        file_put_contents(__DIR__.'/../Storage/'.$filename, serialize($data));
        $process = new Process('/usr/bin/env php queue job '.$tube.' --class='.$class.' --data='.$filename);
        $process->setTimeout(3600);
        $process->start();
        return $process;

    }

    protected function check(&$processes) {

        foreach($processes as &$tube) {
            foreach ($tube as $class => &$process) {
                /** @var Process $process */
                if (!$process->isRunning()) {
                    if (!$process->isSuccessful()) {
                        echo 'EXCEPTION: ' . $process->getErrorOutput() . PHP_EOL;
                    }
                    echo 'COMPLETE: ' . $class . PHP_EOL;
                    $process = null;
                }
            }
            $tube = array_filter($tube, function($process) {
                return null !== $process;
            });
        }
        $processes = array_filter($processes, function($tube) {
            return count($tube) > 0;
        });
    }

    protected function getFileName() {
        return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5)),0,15);
    }
}