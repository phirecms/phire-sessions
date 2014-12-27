<?php

return [
    APP_URI . '/sessions[/]' => [
        'controller' => 'Sessions\Controller\IndexController',
        'action'     => 'index',
        'acl'        => [
            'resource'   => 'sessions',
            'permission' => 'index'
        ]
    ],
    APP_URI . '/users/sessions[/]' => [
        'controller' => 'Sessions\Controller\Users\IndexController',
        'action'     => 'index',
        'acl'        => [
            'resource'   => 'users-sessions',
            'permission' => 'index'
        ]
    ]
];
