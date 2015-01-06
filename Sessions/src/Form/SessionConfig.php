<?php

namespace Sessions\Form;

use Phire\Table;
use Pop\Form\Form;
use Pop\Validator;

class SessionConfig extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  int    $id
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return SessionConfig
     */
    public function __construct($id = null, array $fields, $action = null, $method = 'post')
    {
        $configs = \Sessions\Table\UserSessionConfig::findAll();
        $configsAry = [];

        foreach ($configs->rows() as $config) {
            $configsAry[] = $config->role_id;
        }

        $roles = Table\UserRoles::findAll();
        $roleValues = ['----' => '----'];
        foreach ($roles->rows() as $role) {
            if (!in_array($role->id, $configsAry) || ($id == $role->id)) {
                $roleValues[$role->id] = $role->name;
            }
        }

        $fields[0]['role_id']['value'] = $roleValues;

        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'session-form');
        $this->setIndent('    ');
    }

}