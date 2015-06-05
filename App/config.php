<?php return [
    'balancer' => [
        'enabled' => true,
        'streams' => 4,
        'tubes' => ['SimpleQueueTube']
    ],
    'tubes' => ['SimpleQueueTube']
];