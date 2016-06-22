<?php
/**
 * Module Name: phire-sessions
 * Author: Nick Sagona
 * Description: This is the sessions module for Phire CMS 2
 * Version: 1.0
 */
return [
    'phire-sessions' => [
        'prefix'     => 'Phire\Sessions\\',
        'src'        => __DIR__ . '/../src',
        'routes'     => include 'routes.php',
        'resources'  => include 'resources.php',
        'forms'      => include 'forms.php',
        'nav.phire'  => [
            'sessions' => [
                'name' => 'Sessions',
                'href' => '/sessions',
                'acl'  => [
                    'resource'   => 'sessions',
                    'permission' => 'index'
                ],
                'attributes' => [
                    'class' => 'sessions-nav-icon'
                ],
                'children' => [
                    'logins' => [
                        'name' => 'Logins',
                        'href' => 'logins',
                        'acl'  => [
                            'resource'   => 'sessions',
                            'permission' => 'logins'
                        ]
                    ]
                ]
            ]
        ],
        'nav.module' => [
            'name' => 'Sessions Config',
            'href' => '/sessions/config',
            'acl'  => [
                'resource'   => 'sessions-config',
                'permission' => 'index'
            ]
        ],
        'events' => [
            [
                'name'   => 'app.send.pre',
                'action' => 'Phire\Sessions\Event\UserSession::login'
            ],
            [
                'name'   => 'app.send.pre',
                'action' => 'Phire\Sessions\Event\UserSession::dashboard'
            ],
            [
                'name'   => 'app.dispatch.pre',
                'action' => 'Phire\Sessions\Event\UserSession::logout'
            ]
        ],
        'uninstall' => function(){
            if (isset($_SERVER['REMOTE_ADDR'])) {
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
            }
        },
        'header'                   => __DIR__ . '/../view/phire/header.phtml',
        'footer'                   => __DIR__ . '/../view/phire/footer.phtml',
        'clear_sessions'           => 86400,
        'multiple_session_warning' => false,
        'login_limit'              => 500
    ]
];
