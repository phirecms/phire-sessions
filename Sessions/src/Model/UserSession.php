<?php

namespace Sessions\Model;

use Phire\Model\AbstractModel;
use Pop\Http\Response;
use Pop\Log;
use Pop\Web\Cookie;
use Sessions\Table;

class UserSession extends AbstractModel
{

    /**
     * Get all user sessions
     *
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return array
     */
    public function getAll($limit = null, $page = null, $sort = null)
    {
        $sql = Table\UserSessions::sql();
        $sql->select([
            'id'           => DB_PREFIX . 'user_sessions.id',
            'user_id'      => DB_PREFIX . 'user_sessions.user_id',
            'ip'           => DB_PREFIX . 'user_sessions.ip',
            'ua'           => DB_PREFIX . 'user_sessions.ua',
            'start'        => DB_PREFIX . 'user_sessions.start',
            'username'     => DB_PREFIX . 'users.username'
        ])->join(DB_PREFIX . 'users', [DB_PREFIX . 'users.id' => DB_PREFIX . 'user_sessions.user_id']);

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            $sql->select()->offset($page)->limit($limit);
        }

        $params = [];
        $order  = $this->getSortOrder($sort, $page);
        $by     = explode(' ', $order);
        $sql->select()->orderBy($by[0], $by[1]);

        return (count($params) > 0) ?
            Table\UserSessions::execute((string)$sql, $params)->rows() :
            Table\UserSessions::query((string)$sql)->rows();
    }

    /**
     * Get user session data
     *
     * @param  int $id
     * @return array
     */
    public function getUserData($id)
    {
        $user     = \Phire\Table\Users::findById($id);
        $userData = Table\UserSessionData::findById($id);

        if (isset($userData->user_id)) {
            $data = $userData->getColumns();
            if (null !== $data['logins']) {
                $this->data['logins'] = unserialize($data['logins']);
                krsort($this->data['logins']);
            }
            $this->data['failed_attempts'] = $data['failed_attempts'];
        } else {
            $this->data['logins']          = [];
            $this->data['failed_attempts'] = 0;
        }

        $this->data['username'] = $user->username;
        $this->data['user_id']  = $id;
    }

    /**
     * Remove a user session
     *
     * @param  array $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['rm_sessions'])) {
            foreach ($post['rm_sessions'] as $id) {
                $session = Table\UserSessions::findById((int)$id);
                if (isset($session->id)) {
                    $session->delete();
                }
            }
        }
    }

    /**
     * Clear user data
     *
     * @param  array $post
     * @return void
     */
    public function clear(array $post)
    {
        if (isset($post['user_id'])) {
            $session = Table\UserSessionData::findById((int)$post['user_id']);
            if (isset($session->user_id)) {
                if (isset($post['clear_logins'])) {
                    $session->logins = null;
                }
                if (isset($post['clear_failed_attempts'])) {
                    $session->failed_attempts = 0;
                }
                $session->save();
            }
        }
    }

    /**
     * Determine if list of user sessions have pages
     *
     * @param  int $limit
     * @return boolean
     */
    public function hasPages($limit)
    {
        return (Table\UserSessions::findAll()->count() > $limit);
    }

    /**
     * Get count of user sessions
     *
     * @return int
     */
    public function getCount()
    {
        return Table\UserSessions::findAll()->count();
    }

    /**
     * Alter header view
     *
     * @param  \Phire\Controller\AbstractController $controller
     * @param  \Phire\Application                   $application
     * @return void
     */
    public static function header(\Phire\Controller\AbstractController $controller, \Phire\Application $application)
    {
        $sess = $application->getService('session');
        if (isset($sess->user) && ($controller->hasView())) {
            $controller->view()->phireHeader = __DIR__ . '/../../view/header.phtml';
        }
    }

    /**
     * Dashboard check to display multiple sessions warning
     *
     * @param  \Phire\Controller\AbstractController $controller
     * @param  \Phire\Application                   $application
     * @return void
     */
    public static function dashboard(\Phire\Controller\AbstractController $controller, \Phire\Application $application)
    {
        if (($controller->request()->getRequestUri() == APP_URI) &&
            isset($application->module('Sessions')['multiple_session_warning']) &&
            ($application->module('Sessions')['multiple_session_warning'])) {

            $sess = $application->getService('session');
            if (isset($sess->user) && isset($sess->user->session)) {
                $sql = Table\UserSessions::sql();
                $sql->select()->where('user_id = :user_id')->where('id != :id');
                $session = Table\UserSessions::execute((string)$sql, [
                    'user_id' => $sess->user->id,
                    'id'      => $sess->user->session->id
                ]);
                if (isset($session->id)) {
                    $controller->view()->sessionWarning = true;
                }
            }
        }
    }

    /**
     * Alter user list view
     *
     * @param  \Phire\Controller\AbstractController $controller
     * @return void
     */
    public static function users(\Phire\Controller\AbstractController $controller)
    {
        if ($controller->request()->getRequestUri() == APP_URI . '/users') {
            if (isset($controller->view()->users) && count($controller->view()->users > 0)) {
                $controller->view()->setTemplate(__DIR__ . '/../../view/users/index.phtml');
                foreach ($controller->view()->users as $user) {
                    $userData = Table\UserSessionData::findById($user->id);
                    if (isset($userData->user_id)) {
                        $user->logins = (null !== $userData->logins) ? unserialize($userData->logins) : [];
                        if (count($user->logins) > 0) {
                            end($user->logins);
                            $user->last_login = key($user->logins);
                            $user->last_ip    = $user->logins[$user->last_login]['ip'];
                            reset($user->logins);
                        } else {
                            $user->last_login = null;
                            $user->last_ip    = null;
                        }
                    } else {
                        $user->logins     = [];
                        $user->last_login = null;
                        $user->last_ip    = null;
                    }
                }
            }
        }
    }

    /**
     * Login and track session
     *
     * @param  \Phire\Controller\AbstractController $controller
     * @param  \Phire\Application                   $application
     * @return void
     */
    public static function login(\Phire\Controller\AbstractController $controller, \Phire\Application $application)
    {
        $sess = $application->getService('session');

        $path = BASE_PATH . APP_URI;
        if ($path == '') {
            $path = '/';
        }
        $cookie = Cookie::getInstance(['path' => $path]);
        $cookie->delete('phire_session_timeout');
        $cookie->delete('phire_session_path');

        // If login, validate and start new session
        if (($controller->request()->isPost()) &&
            ($controller->request()->getRequestUri() == APP_URI . '/login')) {
            // If the user successfully logged in
            if (isset($sess->user)) {
                $config = Table\UserSessionConfig::findById($sess->user->role_id);
                $data   = Table\UserSessionData::findById($sess->user->id);
                if (isset($config->role_id)) {
                    if (!self::validate($config, $sess->user, $data)) {
                        if (isset($data->user_id)) {
                            $data->failed_attempts++;
                            $data->save();
                        } else {
                            $data = new Table\UserSessionData([
                                'user_id'         => $sess->user->id,
                                'logins'          => null,
                                'failed_attempts' => 1
                            ]);
                            $data->save();
                        }
                        if (isset($config->role_id) && ((int)$config->log_type > 0) && (null !== $config->log_emails)) {
                            self::log($config, $sess->user, false);
                        }
                        $sess->kill();
                        Response::redirect(BASE_PATH . APP_URI . '/login?failed=' . $data->failed_attempts);
                        exit();
                    } else {
                        if (isset($data->user_id)) {
                            $limit  = (int)$application->module('Sessions')['login_limit'];
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
                                'user_id' => $sess->user->id,
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
                $data = Table\UserSessionData::findById($sess->user->id);
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
                $clear  = (int)$application->module('Sessions')['clear_sessions'];
                if ($clear > 0) {
                    $clear = time() - $clear;
                    $sql   = Table\UserSessions::sql();
                    $sql->delete()->where(['start <= :start']);
                    Table\UserSessions::execute((string)$sql, ['start' => $clear]);
                }

                $session = new Table\UserSessions([
                    'user_id' => $sess->user->id,
                    'ip'      => $_SERVER['REMOTE_ADDR'],
                    'ua'      => $_SERVER['HTTP_USER_AGENT'],
                    'start'   => time()
                ]);
                $session->save();
                $sess->user->session = new \ArrayObject([
                    'id'         => $session->id,
                    'start'      => $session->start,
                    'last'       => $session->start,
                    'expire'     => $expire,
                    'timeout'    => $timeout,
                    'last_login' => $lastLogin,
                    'last_ip'    => $lastIp
                ], \ArrayObject::ARRAY_AS_PROPS);
                if (isset($config->role_id) && ((int)$config->log_type > 0) && (null !== $config->log_emails)) {
                    self::log($config, $sess->user, true);
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
        } else if (isset($sess->user) && isset($sess->user->session)) {
            if ((!isset(Table\UserSessions::findById((int)$sess->user->session->id)->id)) ||
                ((null !== $sess->user->session->expire) &&
                    ((time() - $sess->user->session->last) >= $sess->user->session->expire))) {
                $session = Table\UserSessions::findById((int)$sess->user->session->id);
                if (isset($session->id)) {
                    $session->delete();
                }
                $sess->kill();
                Response::redirect(BASE_PATH . APP_URI . '/login?expired=1');
                exit();
            } else {
                if (($sess->user->session->timeout) && (null !== $sess->user->session->expire)) {
                    $cookie->set('phire_session_timeout', $sess->user->session->expire - 30);
                    $cookie->set('phire_session_path', BASE_PATH . APP_URI);
                }
                $sess->user->session->last = time();
            }
        }
    }

    /**
     * Record logout/session end
     *
     * @param  \Phire\Application $application
     * @return void
     */
    public static function logout(\Phire\Application $application)
    {
        if ($application->router()->getRouteMatch()->getRoute() == APP_URI . '/logout') {
            $path = BASE_PATH . APP_URI;
            if ($path == '') {
                $path = '/';
            }
            $cookie = Cookie::getInstance(['path' => $path]);
            $cookie->delete('phire_session_timeout');
            $cookie->delete('phire_session_path');
            $cookie->delete('phire_session_warning_dismiss');

            $sess = $application->getService('session');
            if (isset($sess->user) && isset($sess->user->session)) {
                $session = Table\UserSessions::findById((int)$sess->user->session->id);
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
                'Someone has logged in as \'' . $user->username . '\' from ' . $_SERVER['REMOTE_ADDR'] :
                'Someone attempted to log in as \'' . $user->username . '\' from ' . $_SERVER['REMOTE_ADDR'];

            $emails = explode(',', $config->log_emails);
            if (in_array($user->email, $emails)) {
                unset($emails[array_search($user->email, $emails)]);
            }
            if (count($emails) > 0) {
                $logger = new Log\Logger(new Log\Writer\Mail($emails, $options));
                $logger->alert($message);
            }
        }
    }

}
