<?php require_once '../vendor/autoload.php';

use Kernel\Helpers;
use Kernel\Queue\Push;

$www = include '../App/http.php';
$jobs = isset($www['jobs']) ? $www['jobs']: [];
$tubes = isset($www['tubes']) ? $www['tubes']: [];
$auth = isset($www['auth']) ? $www['auth'] : null;
$data = $_GET;

$job = $data['job'];
unset($data['job']);
$tube = $data['tube'];
unset($data['tube']);

if (is_callable($auth) && !$auth()) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden';
    exit;
}

if (isset($jobs[$job]) && in_array($tube, $tubes, true) && Helpers::jobExists($jobs[$job]['class'])) {
    $current = $jobs[$job];
    if (!isset($current['data'])) {
        $current['data'] = [];
    }
    if ($current['parameters'] === false) {
        $data = $current['data'];
    } else {
        if (!is_array($current['data'])) {
            $current['data'] = [$current['data']];
        }
        $data = array_merge($current['data'], $data);
    }
    new Push($tube, $current['class'], $data);
    echo $job . ' - added.';
} else {
    header("HTTP/1.0 404 Not Found");
    echo 'Job not found';
}