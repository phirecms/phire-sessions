<?php

namespace Phire\Sessions\Controller\Users;

use Phire\Sessions\Model;
use Phire\Controller\AbstractController;
use Pop\Paginator\Paginator;

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

        $this->prepareView('phire/users/sessions.phtml');
        $this->view->title    = 'Users : Sessions';
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
    public function logins($id)
    {
        $session = new Model\UserSession();
        if ($this->request->isPost()) {
            $session->clear($this->request->getPost());
            $this->sess->setRequestValue('removed', true, 1);
            $this->redirect(BASE_PATH . APP_URI . '/users/logins/' . $id);
        } else {
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

            $this->prepareView('phire/users/logins.phtml');
            $this->view->title           = 'Users : Logins';
            $this->view->pages           = $pages;
            $this->view->logins          = $logins;
            $this->view->total_logins    = count($session->logins);
            $this->view->failed_attempts = $session->failed_attempts;
            $this->view->username        = $session->username;
            $this->view->user_id         = $session->user_id;
            $this->send();
        }
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
        $this->sess->setRequestValue('removed', true, 1);
        $this->redirect(BASE_PATH . APP_URI . '/users/sessions');
    }

    /**
     * Prepare view
     *
     * @param  string $template
     * @return void
     */
    protected function prepareView($template)
    {
        $this->viewPath = __DIR__ . '/../../../view';
        parent::prepareView($template);
    }

}
