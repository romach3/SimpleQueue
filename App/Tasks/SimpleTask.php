<?php namespace App\Tasks;

use Kernel\Queue\Push;

class SimpleTask {

    public function __construct() {
        new Push('SimpleQueueTube', 'SimpleJob', 'Yeah!');
    }

}