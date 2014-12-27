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
                        'href' => BASE_PATH . APP_URI . '/users/sessions',
                        'acl'  => [
                            'resource'   => 'user-sessions',
                            'permission' => 'index'
                        ]
                    ]
                ]
            ]
        ],
        'nav.module' => [
            'name' => 'Sessions',
            'href' => BASE_PATH . APP_URI . '/sessions',
            'acl'  => [
                'resource'   => 'sessions',
                'permission' => 'index'
            ]
        ]
    ]
];
