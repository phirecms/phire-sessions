<?php

namespace Sessions\Controller\Users;

use Sessions\Model;
use Phire\Controller\AbstractController;
use Pop\Http\Response;
use Pop\Paginator\Paginator;

class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
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

        $this->prepareView('users/index.phtml');
        $this->view->title    = 'Users : Sessions';
        $this->view->pages    = $pages;
        $this->view->sessions = $session->getAll($limit, $this->request->getQuery('page'), $this->request->getQuery('sort'));
        $this->send();
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
        Response::redirect(BASE_PATH . APP_URI . '/users/sessions?removed=' . time());
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