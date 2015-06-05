<?php namespace Kernel\Queue;

use Kernel\Contracts\JobInterface;

abstract class JobAbstract implements JobInterface {
    protected $data;
    protected $tube;
    protected $log;

    public function setData($data) {
        $this->data = $data;
    }

    public function setTubeName($tube) {
        $this->tube = $tube;
    }

    abstract public function start();

    public function log($log) {
        if (null === $this->log) {
            $this->log = new \SplFileObject(__DIR__."/../../App/Logs/{$this->tube}.log", "a");
        }
        if (is_array($log)) {
            $log = implode(PHP_EOL, $log);
        }
        $this->log->fwrite($log.PHP_EOL);
    }
}