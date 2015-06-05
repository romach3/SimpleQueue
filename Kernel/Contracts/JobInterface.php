<?php namespace Kernel\Contracts;

interface JobInterface {

    public function setData($data);
    public function setTubeName($tube);
    public function start();

}