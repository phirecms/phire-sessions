<?php

return [
    APP_URI => [
        '/sessions/config[/]' => [
            'controller' => 'Phire\Sessions\Controller\ConfigController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'sessions-config',
                'permission' => 'index'
            ]
        ],
        '/sessions/config/add' => [
            'controller' => 'Phire\Sessions\Controller\ConfigController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'sessions-config',
                'permission' => 'add'
            ]
        ],
        '/sessions/config/edit/:id' => [
            'controller' => 'Phire\Sessions\Controller\ConfigController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'sessions-config',
                'permission' => 'edit'
            ]
        ],
        '/sessions/config/remove' => [
            'controller' => 'Phire\Sessions\Controller\ConfigController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'sessions-config',
                'permission' => 'remove'
            ]
        ],
        '/sessions[/]' => [
            'controller' => 'Phire\Sessions\Controller\IndexController',
            'action'     => 'sessions',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'index'
            ]
        ],
        '/sessions/logins[/:id]' => [
            'controller' => 'Phire\Sessions\Controller\IndexController',
            'action'     => 'logins',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'logins'
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
        '/sessions/remove' => [
            'controller' => 'Phire\Sessions\Controller\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'sessions',
                'permission' => 'remove'
            ]
        ]
    ]
];
