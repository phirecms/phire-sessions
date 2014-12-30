<?php

namespace Sessions\Model;

use Phire\Model\AbstractModel;
use Pop\Http\Response;
use Sessions\Table;

class UserSession extends AbstractModel
{

    /**
     * Record session
     *
     * @param  \Phire\Controller\AbstractController $controller
     * @param  \Phire\Application                   $application
     * @return void
     */
    public static function log(\Phire\Controller\AbstractController $controller, \Phire\Application $application)
    {
        $sess = $application->getService('session');

        // Start new session
        if (($controller->request()->isPost()) && ($controller->request()->getRequestUri() == BASE_PATH . APP_URI . '/login')) {
            // If the user successfully logged in
            if (isset($sess->user)) {
                $config = Table\UserSessionConfigs::findById($sess->user->role_id);
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
                        $sess->kill();
                        Response::redirect(BASE_PATH . APP_URI . '/login?failed=1');
                        exit();
                    } else {
                        if (isset($data->user_id)) {
                            $logins         = unserialize($data->logins);
                            $logins[time()] = [
                                'ua'  => $_SERVER['HTTP_USER_AGENT'],
                                'ip'  => $_SERVER['REMOTE_ADDR']
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
                    $timeout = ((int)$config->timeout_warning > 0)    ? (int)$config->timeout_warning    : null;
                } else {
                    $expire  = null;
                    $timeout = null;
                }
                $session = new Table\UserSessions([
                    'user_id' => $sess->user->id,
                    'ip'      => $_SERVER['REMOTE_ADDR'],
                    'ua'      => $_SERVER['HTTP_USER_AGENT'],
                    'start'   => time()
                ]);
                $session->save();
                $sess->user->session = new \ArrayObject([
                    'id'      => $session->id,
                    'start'   => $session->start,
                    'last'    => $session->start,
                    'expire'  => $expire,
                    'timeout' => $timeout
                ], \ArrayObject::ARRAY_AS_PROPS);
            // Else, if the user login failed
            } else {
                if ((null !== $controller->view()->form) && (null !== $controller->view()->form->username)) {
                    $user = \Phire\Table\Users::findBy(['username' => $controller->view()->form->username]);
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
                    }
                }
            }
        // Check existing session
        } else if (isset($sess->user) && isset($sess->user->session)) {
            if ((!isset(Table\UserSessions::findById((int)$sess->user->session->id)->id)) ||
                ((null !== $sess->user->session->expire) && ((time() - $sess->user->session->last) >= $sess->user->session->expire))) {
                $session = Table\UserSessions::findById((int)$sess->user->session->id);
                if (isset($session->id)) {
                    $session->delete();
                }
                $sess->kill();
                Response::redirect(BASE_PATH . APP_URI . '/login?expired=1');
                exit();
            } else {
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
        if ($application->router()->getRouteMatch()->getRoute() == BASE_PATH . APP_URI . '/logout') {
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
        if (null !== $config->ip_allowed) {
            $ipAllowed = explode(',', $config->ip_allowed);
            if (!in_array($_SERVER['REMOTE_ADDR'], $ipAllowed)) {
                $result = false;
            }
        }

        // Check IP blocked
        if (null !== $config->ip_blocked) {
            $ipBlocked = explode(',', $config->ip_blocked);
            if (in_array($_SERVER['REMOTE_ADDR'], $ipBlocked)) {
                $result = false;
            }
        }

        return $result;
    }

}
