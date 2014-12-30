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
    APP_URI . '/sessions/add' => [
        'controller' => 'Sessions\Controller\IndexController',
        'action'     => 'add',
        'acl'        => [
            'resource'   => 'sessions',
            'permission' => 'add'
        ]
    ],
    APP_URI . '/sessions/edit/:id' => [
        'controller' => 'Sessions\Controller\IndexController',
        'action'     => 'edit',
        'acl'        => [
            'resource'   => 'sessions',
            'permission' => 'edit'
        ]
    ],
    APP_URI . '/sessions/remove' => [
        'controller' => 'Sessions\Controller\IndexController',
        'action'     => 'remove',
        'acl'        => [
            'resource'   => 'sessions',
            'permission' => 'remove'
        ]
    ],
    APP_URI . '/sessions/json' => [
        'controller' => 'Sessions\Controller\IndexController',
        'action'     => 'json',
        'acl'        => [
            'resource'   => 'sessions',
            'permission' => 'json'
        ]
    ],
    APP_URI . '/users/sessions[/]' => [
        'controller' => 'Sessions\Controller\Users\IndexController',
        'action'     => 'index',
        'acl'        => [
            'resource'   => 'users-sessions',
            'permission' => 'index'
        ]
    ],
    APP_URI . '/users/sessions/remove' => [
        'controller' => 'Sessions\Controller\Users\IndexController',
        'action'     => 'remove',
        'acl'        => [
            'resource'   => 'users-sessions',
            'permission' => 'remove'
        ]
    ]
];
