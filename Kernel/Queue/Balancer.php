<?php namespace Kernel\Queue;

use Pheanstalk\Exception\ServerException;

class Balancer {
    protected $config;
    protected $pheanstalk;

    public function __construct() {
        $this->config = Config::get('balancer', [
            'enabled' => false
        ]);
        $this->pheanstalk = Pheanstalk::get();
    }

    public function getTubeName($tube) {
        if (!$this->config['enabled']) {
            return $tube;
        }
        return $this->getTube($tube);
    }

    public function getListenTubes($tube) {
        if (!$this->config['enabled'] || !in_array($tube, $this->config['tubes'], true)) {
            return [$tube];
        }
        $response = [];
        for ($i = 1; $i <= $this->config['streams']; $i++) {
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
            for ($i = 1; $i <= $this->config['streams']; $i++) {
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