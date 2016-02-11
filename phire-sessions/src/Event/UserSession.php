<?php

namespace Phire\Sessions\Event;

use Phire\Controller\AbstractController;
use Pop\Application;
use Pop\Http\Response;
use Pop\Log;
use Pop\Web\Cookie;
use Phire\Sessions\Table;

class UserSession
{

    /**
     * Dashboard check to display multiple sessions warning
     *
     * @param  AbstractController $controller
     * @param  Application        $application
     * @return void
     */
    public static function dashboard(AbstractController $controller, Application $application)
    {
        $sess    = $application->getService('session');
        $userUri = APP_URI;
        $key     = 'user';

        if (isset($sess->member) && $application->isRegistered('phire-members')) {
            $key = 'member';
            $memberAdmin = new \Phire\Members\Model\MembersAdmin();
            $memberAdmin->getByRoleId($sess->member->role_id);
            if (isset($memberAdmin->uri)) {
                $userUri = $memberAdmin->uri;
            }
        }

        if (($controller->request()->getRequestUri() == $userUri) &&
            isset($application->module('phire-sessions')['multiple_session_warning']) &&
            ($application->module('phire-sessions')['multiple_session_warning'])) {
            if (isset($sess[$key]) && isset($sess[$key]->session)) {
                $sql = Table\UserSessions::sql();
                $sql->select()->where('user_id = :user_id')->where('id != :id');
                $session = Table\UserSessions::execute((string)$sql, [
                    'user_id' => $sess[$key]->id,
                    'id'      => $sess[$key]->session->id
                ]);
                if (isset($session->id)) {
                    $controller->view()->sessionWarning = true;
                }
            }
        }
    }

    /**
     * Login and track session
     *
     * @param  AbstractController $controller
     * @param  Application        $application
     * @return void
     */
    public static function login(AbstractController $controller, Application $application)
    {
        $sess    = $application->getService('session');
        $userUri = APP_URI;
        $key     = 'user';

        if (isset($sess->member) && $application->isRegistered('phire-members')) {
            $key = 'member';
            $memberAdmin = new \Phire\Members\Model\MembersAdmin();
            $memberAdmin->getByRoleId($sess->member->role_id);
            if (isset($memberAdmin->uri)) {
                $userUri = $memberAdmin->uri;
            }
        }

        $path = BASE_PATH . $userUri;
        if ($path == '') {
            $path = '/';
        }

        $cookie = Cookie::getInstance(['path' => $path]);
        $cookie->delete('phire_session_timeout');
        $cookie->delete('phire_session_path');

        // If login, validate and start new session
        if (($controller->request()->isPost()) &&
            (substr($controller->request()->getRequestUri(), -6) == '/login')) {
            // If the user successfully logged in
            if (isset($sess[$key])) {
                $config = Table\UserSessionConfig::findById($sess[$key]->role_id);
                $data   = Table\UserSessionData::findById($sess[$key]->id);
                if (isset($config->role_id)) {
                    if (!self::validate($config, $sess[$key], $data)) {
                        if (isset($data->user_id)) {
                            $data->failed_attempts++;
                            $data->save();
                        } else {
                            $data = new Table\UserSessionData([
                                'user_id'         => $sess[$key]->id,
                                'logins'          => null,
                                'failed_attempts' => 1
                            ]);
                            $data->save();
                        }
                        if (isset($config->role_id) && ((int)$config->log_type > 0) && (null !== $config->log_emails)) {
                            self::log($config, $sess[$key], false);
                        }
                        $sess->kill();
                        Response::redirect(BASE_PATH . $userUri . '/login?failed=' . $data->failed_attempts);
                        exit();
                    } else {
                        if (isset($data->user_id)) {
                            $limit  = (int)$application->module('phire-sessions')['login_limit'];
                            $logins = unserialize($data->logins);
                            if (($limit > 0) && (count($logins) >= $limit)) {
                                reset($logins);
                                unset($logins[key($logins)]);
                            }
                            $logins[time()] = [
                                'ua' => $_SERVER['HTTP_USER_AGENT'],
                                'ip' => $_SERVER['REMOTE_ADDR']
                            ];
                            $data->failed_attempts = 0;
                            $data->logins          = serialize($logins);
                            $data->save();
                        } else {
                            $data = new Table\UserSessionData([
                                'user_id' => $sess[$key]->id,
                                'logins'  => serialize([time() => [
                                    'ua'  => $_SERVER['HTTP_USER_AGENT'],
                                    'ip'  => $_SERVER['REMOTE_ADDR']
                                ]]),
                                'failed_attempts' => 0
                            ]);
                            $data->save();
                        }
                    }
                    $expire  = ((int)$config->session_expiration > 0) ? (int)$config->session_expiration : null;
                    $timeout = ((int)$config->timeout_warning);
                } else {
                    $expire  = null;
                    $timeout = false;
                }

                $lastLogin = null;
                $lastIp    = null;

                // Check for the last login
                $data = Table\UserSessionData::findById($sess[$key]->id);
                if (isset($data->user_id)) {
                    $logins = (null !== $data->logins) ? unserialize($data->logins) : [];
                    if (count($logins) > 1) {
                        $keys      = array_keys($logins);
                        $timestamp = (isset($keys[count($keys) - 2])) ? $keys[count($keys) - 2] : null;
                        if ((null !== $timestamp) && isset($logins[$timestamp])) {
                            $lastLogin = $timestamp;
                            $lastIp    = $logins[$timestamp]['ip'];
                        }
                    }
                }

                // Clear old sessions
                $clear  = (int)$application->module('phire-sessions')['clear_sessions'];
                if ($clear > 0) {
                    $clear = time() - $clear;
                    $sql   = Table\UserSessions::sql();
                    $sql->delete()->where(['start <= :start']);
                    Table\UserSessions::execute((string)$sql, ['start' => $clear]);
                }

                $session = new Table\UserSessions([
                    'user_id' => $sess[$key]->id,
                    'ip'      => $_SERVER['REMOTE_ADDR'],
                    'ua'      => $_SERVER['HTTP_USER_AGENT'],
                    'start'   => time()
                ]);
                $session->save();
                $sess[$key]->session = new \ArrayObject([
                    'id'         => $session->id,
                    'start'      => $session->start,
                    'last'       => $session->start,
                    'expire'     => $expire,
                    'timeout'    => $timeout,
                    'last_login' => $lastLogin,
                    'last_ip'    => $lastIp
                ], \ArrayObject::ARRAY_AS_PROPS);
                if (isset($config->role_id) && ((int)$config->log_type > 0) && (null !== $config->log_emails)) {
                    self::log($config, $sess[$key], true);
                }
            // Else, if the user login failed
            } else {
                if ((null !== $controller->view()->form) && (null !== $controller->view()->form->username)) {
                    $user   = \Phire\Table\Users::findBy(['username' => $controller->view()->form->username]);
                    $config = Table\UserSessionConfig::findById($user->role_id);
                    if (isset($user->id)) {
                        $data = Table\UserSessionData::findById($user->id);
                        if (isset($data->user_id)) {
                            $data->failed_attempts++;
                            $data->save();
                        } else {
                            $data = new Table\UserSessionData([
                                'user_id'         => $user->id,
                                'logins'          => null,
                                'failed_attempts' => 1
                            ]);
                            $data->save();
                        }
                        if (isset($config->role_id) && ((int)$config->log_type > 0) && (null !== $config->log_emails)) {
                            self::log($config, $user, false);
                        }
                    }
                }
            }
        // Check existing session
        } else if (isset($sess[$key]) && isset($sess[$key]->session)) {
            if ((!isset(Table\UserSessions::findById((int)$sess[$key]->session->id)->id)) ||
                ((null !== $sess[$key]->session->expire) &&
                    ((time() - $sess[$key]->session->last) >= $sess[$key]->session->expire))) {
                $session = Table\UserSessions::findById((int)$sess[$key]->session->id);
                if (isset($session->id)) {
                    $session->delete();
                }
                $sess->kill();
                Response::redirect(BASE_PATH . $userUri . '/login?expired=1');
                exit();
            } else {
                if (($sess[$key]->session->timeout) && (null !== $sess[$key]->session->expire)) {
                    $cookie->set('phire_session_timeout', $sess[$key]->session->expire - 30);
                    $cookie->set('phire_session_path', BASE_PATH . $userUri);
                }
                $sess[$key]->session->last = time();
            }
        }
    }

    /**
     * Record logout/session end
     *
     * @param  \Pop\Application $application
     * @return void
     */
    public static function logout(Application $application)
    {
        $sess    = $application->getService('session');
        $userUri = APP_URI;
        $key     = 'user';

        if (isset($sess->member) && $application->isRegistered('phire-members')) {
            $key = 'member';
            $memberAdmin = new \Phire\Members\Model\MembersAdmin();
            $memberAdmin->getByRoleId($sess->member->role_id);
            if (isset($memberAdmin->uri)) {
                $userUri = $memberAdmin->uri;
            }
        }

        if ($application->router()->getRouteMatch()->getRoute() == $userUri . '/logout') {
            $path = BASE_PATH . APP_URI;
            if ($path == '') {
                $path = '/';
            }
            $cookie = Cookie::getInstance(['path' => $path]);
            $cookie->delete('phire_session_timeout');
            $cookie->delete('phire_session_path');
            $cookie->delete('phire_session_warning_dismiss');

            $sess = $application->getService('session');
            if (isset($sess[$key]) && isset($sess[$key]->session)) {
                $session = Table\UserSessions::findById((int)$sess[$key]->session->id);
                if (isset($session->id)) {
                    $session->delete();
                }
            }
        }
    }

    /**
     * Validate attempted session
     *
     * @param  mixed $config
     * @param  mixed $user
     * @param  mixed $data
     * @return boolean
     */
    public static function validate($config, $user, $data)
    {
        $result = true;

        // Check for multiple sessions
        if ((!$config->multiple_sessions) && isset(Table\UserSessions::findBy(['user_id' => $user->id])->id)) {
            $result = false;
        }
        // Check for too many failed attempts
        if ($config->allowed_attempts > 0) {
            if (isset($data->user_id) && ($data->failed_attempts >= $config->allowed_attempts)) {
                $result = false;
            }
        }
        // Check IP allowed
        if (!empty($config->ip_allowed)) {
            $ipAllowed = explode(',', $config->ip_allowed);
            if (!in_array($_SERVER['REMOTE_ADDR'], $ipAllowed)) {
                $result = false;
            }
        }
        // Check IP blocked
        if (!empty($config->ip_blocked)) {
            $ipBlocked = explode(',', $config->ip_blocked);
            if (in_array($_SERVER['REMOTE_ADDR'], $ipBlocked)) {
                $result = false;
            }
        }

        return $result;
    }


    /**
     * Log session
     *
     * @param  mixed   $config
     * @param  mixed   $user
     * @param  boolean $success
     * @return void
     */
    public static function log($config, $user, $success)
    {
        if (($config->log_type == 3) || (($config->log_type == 2) && ($success)) ||
            (($config->log_type == 1) && (!$success))) {
            $domain  = str_replace('www.', '', $_SERVER['HTTP_HOST']);
            $noreply = 'noreply@' . $domain;
            $options = [
                'subject' => (($success) ? 'Successful' : 'Failed') .
                    ' Login (' . $domain . ') : Phire CMS Session Notification',
                'headers' => [
                    'From'     => $noreply . ' <' . $noreply . '>',
                    'Reply-To' => $noreply . ' <' . $noreply . '>'
                ]
            ];

            $message = ($success) ?
                'Someone has logged in at ' . $_SERVER['REQUEST_URI'] . ' as \'' . $user->username . '\' from ' . $_SERVER['REMOTE_ADDR'] :
                'Someone attempted to log in at ' . $_SERVER['REQUEST_URI'] . ' as \'' . $user->username . '\' from ' . $_SERVER['REMOTE_ADDR'];

            $emails = explode(',', $config->log_emails);
            if (count($emails) > 0) {
                $logger = new Log\Logger(new Log\Writer\Mail($emails, $options));
                $logger->alert($message);
            }
        }
    }

}
