<?php namespace Kernel\Queue;

use Pheanstalk\Exception\ServerException;

class Thread {
    protected $pheanstalk;

    public function __construct() {
        $this->pheanstalk = Pheanstalk::get();
    }

    public function getTubeName($tube) {
        if (!Config::get('thread.enabled', false)) {
            return $tube;
        }
        return $this->getTube($tube);
    }

    public function getListenTubes($tube) {
        if (!Config::get('thread.enabled', false) || !in_array($tube, Config::get('thread.tubes', []), true)) {
            return [$tube];
        }
        $response = [];
        for ($i = 1; $i <= Config::get('thread.threads', 4); $i++) {
            $response[] = $tube . '___' . $i;
        }
        return $response;
    }

    protected function getTube($tube) {
        $stats = $this->getStats($tube);
        $min = 9999;
        $name = '';
        foreach($stats as $tube => $data) {
            if ($data['current-jobs-ready'] < $min) {
                $name = $tube;
                $min = $data['current-jobs-ready'];
            }
        }
        return $name;
    }

    protected function getStats($tube) {
        $stats = [];
        $name = $tube . '___' . 1;
        try {
            for ($i = 1; $i <= Config::get('thread.threads', 4); $i++) {
                $name = $tube . '___' . $i;
                $response = $this->pheanstalk->statsTube($name);
                $stats[$name] = $response;
            }
        } catch (ServerException $e) {
            if (count($stats) === 0) {
                $stats[$name] = ['current-jobs-ready' => 0];
            }
        }
        return $stats;
    }

}