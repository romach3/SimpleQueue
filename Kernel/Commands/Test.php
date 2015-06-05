<?php namespace Kernel\Commands;


class Test {

    public function __invoke() {
        if (class_exists('App\Tasks\SimpleTask') && class_exists('App\Jobs\SimpleJob')) {
            new \App\Tasks\SimpleTask();
            echo 'see App/Logs/SimpleQueueTube.log'.PHP_EOL;
        } else {
            echo 'App\Tasks\SimpleTask and App\Jobs\SimpleJob not found'.PHP_EOL;
        }
    }

}