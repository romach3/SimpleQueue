<?php return [
    'jobs' => [
        'simple-job' => [
            'class' => 'SimpleJob',
            'parameters' => true,
            'data' => 'Yeah!',
        ]
    ],
    'tubes' => ['SimpleQueueTube'],
    'auth' => function() {
        return false;
    }
];