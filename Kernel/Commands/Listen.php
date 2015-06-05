<?php namespace Kernel\Commands;

use Kernel\Helpers;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Process\Process;

class Listen {
    protected $pheanstalk;

    public function __construct() {
        $this->pheanstalk = new Pheanstalk('127.0.0.1');
    }

    public function __invoke($tube) {
        while(true) {
            $job = $this->pheanstalk
                ->watch($tube)
                ->ignore('default')
                ->reserve();
            $data = $job->getData();
            $this->pheanstalk->delete($job);
            $task = unserialize($data);
            if (isset($task['class']) && isset($task['data']) && Helpers::jobExists($task['class'])) {
                $this->start($tube, $task['class'], $task['data']);
            } else {
                echo 'ERROR: '.$task['class'].PHP_EOL;
            }
            usleep(200);
        }
    }

    protected function start($tube, $class, $data) {
        $filename = $this->getFileName();
        file_put_contents(__DIR__.'/../Storage/'.$filename, serialize($data));
        $process = new Process('/usr/bin/env php queue job '.$tube.' --class='.$class.' --data='.$filename);
        $process->setTimeout(3600);
        $process->run();
        if (!$process->isSuccessful()) {
            echo 'EXCEPTION: '.$process->getErrorOutput().PHP_EOL;
        }
        echo 'COMPLETE: '.$class.PHP_EOL;
    }

    protected function getFileName() {
        return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5)),0,15);
    }
}