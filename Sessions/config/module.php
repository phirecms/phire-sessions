<?php
/**
 * Module Name: Sessions
 * Author: Nick Sagona
 * Description: This is the sessions module for Phire CMS 2
 * Version: 1.0
 */
return [
    'Sessions' => [
        'prefix'     => 'Sessions\\',
        'src'        => __DIR__ . '/../src',
        'routes'     => include 'routes.php',
        'resources'  => include 'resources.php',
        'nav.phire'  => [
            'users' => [
                'children' => [
                    'sessions' => [
                        'name' => 'Sessions',
                        'href' => '/users/sessions',
                        'acl'  => [
                            'resource'   => 'users-sessions',
                            'permission' => 'index'
                        ]
                    ]
                ]
            ]
        ],
        'nav.module' => [
            'name' => 'Sessions',
            'href' => '/sessions',
            'acl'  => [
                'resource'   => 'sessions',
                'permission' => 'index'
            ]
        ],
        'events' => [
            [
                'name'   => 'app.send',
                'action' => 'Sessions\Model\UserSession::login'
            ],
            [
                'name'   => 'app.dispatch.pre',
                'action' => 'Sessions\Model\UserSession::logout'
            ]
        ]
    ]
];
