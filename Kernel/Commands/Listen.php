<?php namespace Kernel\Commands;

use Kernel\Helpers;
use Kernel\Queue\Balancer;
use Kernel\Queue\Config;
use Kernel\Queue\Pheanstalk;
use Symfony\Component\Process\Process;

class Listen {
    protected $pheanstalk;

    public function __construct() {
        $this->pheanstalk = Pheanstalk::get();
    }

    public function __invoke($tube = null) {
        $balancer = new Balancer();
        $listen = [];
        if (null === $tube) {
            $tubes = Config::get('tubes', []);
            foreach($tubes as $tube) {
                $listen[] = $balancer->getListenTubes($tube);
            }
        } else {
            $listen = [$balancer->getListenTubes($tube)];
        }
        while(true) {
            foreach($listen as $tubes) {
                $processes = [];
                foreach($tubes as $tube) {
                    $job = $this->pheanstalk
                        ->watch($tube)
                        ->ignore('default')
                        ->reserve();
                    $data = $job->getData();
                    $this->pheanstalk->delete($job);
                    $task = unserialize($data);
                    if (isset($task['class']) && isset($task['data']) && Helpers::jobExists($task['class'])) {
                        $processes[$task['class']] = $this->start($tube, $task['class'], $task['data']);
                    } else {
                        echo 'ERROR: ' . $task['class'] . PHP_EOL;
                    }
                    usleep(200);
                }
                foreach($processes as $class => $process) {
                    $this->wait($process, $class);
                }
            }
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

    protected function wait(Process $process, $class) {
        while ($process->isRunning()) {
            usleep(200);
        }
        if (!$process->isSuccessful()) {
            echo 'EXCEPTION: '.$process->getErrorOutput().PHP_EOL;
        }
        echo 'COMPLETE: '.$class.PHP_EOL;
    }

    protected function getFileName() {
        return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5)),0,15);
    }
}