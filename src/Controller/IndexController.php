<?php
/**
 * Phire Sessions Module
 *
 * @link       https://github.com/phirecms/phire-sessions
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Sessions\Controller;

use Phire\Sessions\Model;
use Phire\Controller\AbstractController;
use Pop\Paginator\Paginator;

/**
 * Sessions Index Controller class
 *
 * @category   Phire\Sessions
 * @package    Phire\Sessions
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class IndexController extends AbstractController
{

    /**
     * Sessions action method
     *
     * @return void
     */
    public function sessions()
    {
        $session = new Model\UserSession();

        if ($session->hasPages($this->config->pagination)) {
            $limit = $this->config->pagination;
            $pages = new Paginator($session->getCount(), $limit);
            $pages->useInput(true);
        } else {
            $limit = null;
            $pages = null;
        }

        $this->prepareView('index.phtml');
        $this->view->title    = 'Sessions';
        $this->view->pages    = $pages;
        $this->view->sessions = $session->getAll(
            $limit, $this->request->getQuery('page'), $this->request->getQuery('sort')
        );
        $this->send();
    }

    /**
     * Logins action method
     *
     * @param  int $id
     * @return void
     */
    public function logins($id = null)
    {
        $session = new Model\UserSession();
        if ($this->request->isPost()) {
            $session->clear($this->request->getPost());
            $this->sess->setRequestValue('removed', true);
            $this->redirect(BASE_PATH . APP_URI . '/sessions/logins/' . $id);
        } else {
            if (null !== $id) {
                $session = new Model\UserSession();
                $session->getUserData($id);

                if (count($session->logins) > $this->config->pagination) {
                    $page  = $this->request->getQuery('page');
                    $limit = $this->config->pagination;
                    $pages = new Paginator(count($session->logins), $limit);
                    $pages->useInput(true);

                    $offset = ((null !== $page) && ((int)$page > 1)) ?
                        ($page * $limit) - $limit : 0;
                    $logins = array_slice($session->logins, $offset, $limit, true);
                } else {
                    $pages  = null;
                    $logins = $session->logins;
                }

                $this->prepareView('user-logins.phtml');
                $this->view->title           = 'Sessions : Logins';
                $this->view->pages           = $pages;
                $this->view->logins          = $logins;
                $this->view->total_logins    = count($session->logins);
                $this->view->failed_attempts = $session->failed_attempts;
                $this->view->username        = $session->username;
                $this->view->user_id         = $session->user_id;
                $this->send();
            } else {
                $this->prepareView('logins.phtml');
                $this->view->title = 'Sessions : Logins';

                $user = new \Phire\Model\User();

                if ($user->hasPages($this->config->pagination, null, null, [])) {
                    $limit = $this->config->pagination;
                    $pages = new Paginator($user->getCount(null, null, []), $limit);
                    $pages->useInput(true);
                } else {
                    $limit = null;
                    $pages = null;
                }

                $users = $user->getAll(
                    null, null, [], $limit,
                    $this->request->getQuery('page'), $this->request->getQuery('sort')
                );

                foreach ($users as $k => $u) {
                    $session = new Model\UserSession();
                    $session->getUserData($u->id);
                    $users[$k]->logins = (null !== $session->logins) ? $session->logins : [];
                }

                $this->view->users = $users;
                $this->view->pages = $pages;
                $this->send();
            }
        }
    }

    /**
     * JSON action method to ping the session to prevent logout
     *
     * @return void
     */
    public function json()
    {
        if (isset($this->sess->user) && isset($this->sess->user->session)) {
            $this->sess->user->session->last = time();
            $json = ['success' => 1];
        } else {
            $json = ['success' => 0];
        }

        $this->response->setBody(json_encode($json, JSON_PRETTY_PRINT));
        $this->send(200, ['Content-Type' => 'application/json']);
    }

    /**
     * Remove action method
     *
     * @return void
     */
    public function remove()
    {
        if ($this->request->isPost()) {
            $session = new Model\UserSession();
            $session->remove($this->request->getPost());
        }
        $this->sess->setRequestValue('removed', true);
        $this->redirect(BASE_PATH . APP_URI . '/sessions');
    }

    /**
     * Prepare view
     *
     * @param  string $template
     * @return void
     */
    protected function prepareView($template)
    {
        $this->viewPath = __DIR__ . '/../../view/sessions';
        parent::prepareView($template);
    }

}
