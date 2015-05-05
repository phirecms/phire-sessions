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
        'forms'      => include 'forms.php',
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
                'action' => 'Sessions\Event\UserSession::login'
            ],
            [
                'name'   => 'app.send',
                'action' => 'Sessions\Event\UserSession::dashboard'
            ],
            [
                'name'   => 'app.send',
                'action' => 'Sessions\Event\UserSession::users'
            ],
            [
                'name'   => 'app.dispatch.pre',
                'action' => 'Sessions\Event\UserSession::logout'
            ]
        ],
        'install' => function(){
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire')) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire');
                chmod($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire', 0777);
            }
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire/users')) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire/users');
                chmod($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire/users', 0777);
            }
            copy(__DIR__ . '/../view/phire/header.phtml', $_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire/header.phtml');
            copy(__DIR__ . '/../view/phire/users/index.phtml', $_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire/users/index.phtml');
            chmod($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire/header.phtml', 0777);
            chmod($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire/users/index.phtml', 0777);
        },
        'uninstall' => function(){
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire/header.phtml')) {
                unlink($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire/header.phtml');
            }
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire/users/index.phtml')) {
                unlink($_SERVER['DOCUMENT_ROOT'] . MODULE_PATH . '/phire/view/phire/users/index.phtml');
            }

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
