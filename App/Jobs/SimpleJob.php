<?php namespace App\Jobs;

use Kernel\Queue\JobAbstract;

class SimpleJob extends JobAbstract {

    public function start() {
        $this->log($this->data);
    }

}