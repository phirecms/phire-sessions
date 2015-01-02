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
                'action' => 'Sessions\Model\UserSession::header'
            ],
            [
                'name'   => 'app.send',
                'action' => 'Sessions\Model\UserSession::login'
            ],
            [
                'name'   => 'app.send',
                'action' => 'Sessions\Model\UserSession::dashboard'
            ],
            [
                'name'   => 'app.send',
                'action' => 'Sessions\Model\UserSession::users'
            ],
            [
                'name'   => 'app.dispatch.pre',
                'action' => 'Sessions\Model\UserSession::logout'
            ]
        ],
        'uninstall' => function(){
            $path = BASE_PATH . APP_URI;
            if ($path == '') {
                $path = '/';
            }
            $cookie = \Pop\Web\Cookie::getInstance(['path' => $path]);
            $cookie->delete('phire_session_timeout');
            $cookie->delete('phire_session_path');

            $sess = \Pop\Web\Session::getInstance();
            if (isset($sess->user) && isset($sess->user->session)) {
                unset($sess->user->session);
            }
        },
        'clear_sessions'           => 86400,
        'multiple_session_warning' => false,
        'login_limit'              => 500
    ]
];
