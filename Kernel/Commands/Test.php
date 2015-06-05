<?php namespace Kernel\Commands;


use Kernel\Queue\Config;

class Test {

    public function __invoke() {
        if (!file_exists(__DIR__.'/../../App/config.php')) {
            copy(__DIR__.'/../../App/config_default.php', __DIR__.'/../../App/config.php');
        }
        if (!file_exists(__DIR__.'/../../App/http.php')) {
            copy(__DIR__.'/../../App/http_default.php', __DIR__.'/../../App/http.php');
        }
        Config::reload();
        if (class_exists('App\Tasks\SimpleTask') && class_exists('App\Jobs\SimpleJob')) {
            new \App\Tasks\SimpleTask();
            echo 'see App/Logs/SimpleQueueTube.log'.PHP_EOL;
        } else {
            echo 'App\Tasks\SimpleTask and App\Jobs\SimpleJob not found'.PHP_EOL;
        }
    }

}