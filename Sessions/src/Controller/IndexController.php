<?php

namespace Sessions\Controller;

use Sessions\Model;
use Sessions\Form;
use Sessions\Table;
use Phire\Controller\AbstractController;
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
        $session = new Model\SessionConfig();

        if ($session->hasPages($this->config->pagination)) {
            $limit = $this->config->pagination;
            $pages = new Paginator($session->getCount(), $limit);
            $pages->useInput(true);
        } else {
            $limit = null;
            $pages = null;
        }

        $this->prepareView('index.phtml');
        $this->view->title          = 'Modules : Sessions';
        $this->view->pages          = $pages;
        $this->view->sessions       = $session->getAll(
            $limit, $this->request->getQuery('page'), $this->request->getQuery('sort')
        );
        $this->view->rolesAvailable = $session->rolesAvailable();

        $this->send();
    }

    /**
     * Add action method
     *
     * @return void
     */
    public function add()
    {
        if ((new Model\SessionConfig())->rolesAvailable()) {
            $this->prepareView('add.phtml');
            $this->view->title = 'Modules : Sessions : Add';

            $form = new Form\SessionConfig(null, $this->application->config()['forms']['Sessions\Form\SessionConfig']);

            if ($this->request->isPost()) {
                $form->addFilter('strip_tags')
                    ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                    ->setFieldValues($this->request->getPost());

                if ($form->isValid()) {
                    $form->clearFilters()
                        ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                        ->filter();
                    $session = new Model\SessionConfig();
                    $session->save($form->getFields());

                    $this->redirect(BASE_PATH . APP_URI . '/sessions/edit/' . $session->role_id . '?saved=' . time());
                }
            }

            $this->view->form = $form;
            $this->send();
        } else {
            $this->redirect(BASE_PATH . APP_URI . '/sessions');
        }
    }

    /**
     * Edit action method
     *
     * @param  int $id
     * @return void
     */
    public function edit($id)
    {
        $session = new Model\SessionConfig();
        $session->getById($id);

        $this->prepareView('edit.phtml');
        $this->view->title          = 'Modules : Sessions : Edit';
        $this->view->role           = $session->role;
        $this->view->rolesAvailable = $session->rolesAvailable();

        $form = new Form\SessionConfig($id, $this->application->config()['forms']['Sessions\Form\SessionConfig']);
        $form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($session->toArray());

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
                 ->setFieldValues($this->request->getPost());

            if ($form->isValid()) {
                $form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();
                $session = new Model\SessionConfig();
                $session->update($form->getFields());
                $this->redirect(BASE_PATH . APP_URI . '/sessions/edit/' . $session->role_id . '?saved=' . time());
            }
        }

        $this->view->form = $form;
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
            $session = new Model\SessionConfig();
            $session->remove($this->request->getPost());
        }
        $this->redirect(BASE_PATH . APP_URI . '/sessions?removed=' . time());
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
     * Prepare view
     *
     * @param  string $template
     * @return void
     */
    protected function prepareView($template)
    {
        $this->viewPath = __DIR__ . '/../../view';
        parent::prepareView($template);
    }

}
