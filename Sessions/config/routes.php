<?php

return [
    APP_URI => [
        '/sessions[/]' => [
            'controller' => 'Sessions\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'index'
            ]
        ],
        '/sessions/add' => [
            'controller' => 'Sessions\Controller\IndexController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'add'
            ]
        ],
        '/sessions/edit/:id' => [
            'controller' => 'Sessions\Controller\IndexController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'edit'
            ]
        ],
        '/sessions/remove' => [
            'controller' => 'Sessions\Controller\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'remove'
            ]
        ],
        '/sessions/json' => [
            'controller' => 'Sessions\Controller\IndexController',
            'action'     => 'json',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'json'
            ]
        ],
        '/users/sessions[/]' => [
            'controller' => 'Sessions\Controller\Users\IndexController',
            'action'     => 'sessions',
            'acl'        => [
                'resource'   => 'users-sessions',
                'permission' => 'sessions'
            ]
        ],
        '/users/logins/:id' => [
            'controller' => 'Sessions\Controller\Users\IndexController',
            'action'     => 'logins',
            'acl'        => [
                'resource'   => 'users-sessions',
                'permission' => 'logins'
            ]
        ],
        '/users/sessions/remove' => [
            'controller' => 'Sessions\Controller\Users\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'users-sessions',
                'permission' => 'remove'
            ]
        ]
    ]
];
