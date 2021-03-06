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
use Phire\Sessions\Form;
use Phire\Sessions\Table;
use Phire\Controller\AbstractController;
use Pop\Paginator\Paginator;

/**
 * Sessions Config Controller class
 *
 * @category   Phire\Sessions
 * @package    Phire\Sessions
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class ConfigController extends AbstractController
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

        $this->prepareView('config/index.phtml');
        $this->view->title          = 'Sessions Config';
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
            $this->prepareView('config/add.phtml');
            $this->view->title = 'Sessions Config : Add';

            $this->view->form = new Form\SessionConfig(null, $this->application->config()['forms']['Phire\Sessions\Form\SessionConfig']);

            if ($this->request->isPost()) {
                $this->view->form->addFilter('strip_tags')
                    ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                    ->setFieldValues($this->request->getPost());

                if ($this->view->form->isValid()) {
                    $this->view->form->clearFilters()
                        ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                        ->filter();
                    $session = new Model\SessionConfig();
                    $session->save($this->view->form->getFields());
                    $this->view->id = $session->role_id;
                    $this->sess->setRequestValue('saved', true);
                    $this->redirect(BASE_PATH . APP_URI . '/sessions/config/edit/' . $session->role_id);
                }
            }

            $this->send();
        } else {
            $this->redirect(BASE_PATH . APP_URI . '/sessions/config');
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

        $this->prepareView('config/edit.phtml');
        $this->view->title          = 'Sessions Config';
        $this->view->role           = $session->role;
        $this->view->rolesAvailable = $session->rolesAvailable();

        $this->view->form = new Form\SessionConfig($id, $this->application->config()['forms']['Phire\Sessions\Form\SessionConfig']);
        $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($session->toArray());

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();
                $session = new Model\SessionConfig();
                $session->update($this->view->form->getFields());
                $this->view->id = $session->role_id;
                $this->sess->setRequestValue('saved', true);
                $this->redirect(BASE_PATH . APP_URI . '/sessions/config/edit/' . $session->role_id);
            }
        }

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
        $this->sess->setRequestValue('removed', true);
        $this->redirect(BASE_PATH . APP_URI . '/sessions/config');
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
