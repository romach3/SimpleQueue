<?php namespace Kernel\Commands;

use Kernel\Contracts\JobInterface;

class Job {

    public function __invoke($tube, $class, $data) {
        $filename = $data;
        $data = unserialize(file_get_contents(__DIR__.'/../Storage/'.$filename));
        unlink(__DIR__.'/../Storage/'.$filename);
        if (substr($class, 0, 1) !== '\\') {
            $class = '\App\Jobs\\'.$class;
        }
        /** @var JobInterface $job */
        $job = new $class;
        $job->setData($data);
        $job->setTubeName($tube);
        $job->start();
    }

}