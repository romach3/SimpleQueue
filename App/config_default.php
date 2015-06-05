<?php return [
    'balancer' => [
        'enabled' => false,
        'streams' => 4,
        'tubes' => ['SimpleQueueTube']
    ],
    'tubes' => ['SimpleQueueTube'],
    'pause' => 200
];