<?php

return [
    APP_URI => [
        '/sessions[/]' => [
            'controller' => 'Phire\Sessions\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'index'
            ]
        ],
        '/sessions/add' => [
            'controller' => 'Phire\Sessions\Controller\IndexController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'add'
            ]
        ],
        '/sessions/edit/:id' => [
            'controller' => 'Phire\Sessions\Controller\IndexController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'edit'
            ]
        ],
        '/sessions/remove' => [
            'controller' => 'Phire\Sessions\Controller\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'remove'
            ]
        ],
        '/sessions/json' => [
            'controller' => 'Phire\Sessions\Controller\IndexController',
            'action'     => 'json',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'json'
            ]
        ],
        '/users/sessions[/]' => [
            'controller' => 'Phire\Sessions\Controller\Users\IndexController',
            'action'     => 'sessions',
            'acl'        => [
                'resource'   => 'users-sessions',
                'permission' => 'sessions'
            ]
        ],
        '/users/logins/:id' => [
            'controller' => 'Phire\Sessions\Controller\Users\IndexController',
            'action'     => 'logins',
            'acl'        => [
                'resource'   => 'users-sessions',
                'permission' => 'logins'
            ]
        ],
        '/users/sessions/remove' => [
            'controller' => 'Phire\Sessions\Controller\Users\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'users-sessions',
                'permission' => 'remove'
            ]
        ]
    ]
];
