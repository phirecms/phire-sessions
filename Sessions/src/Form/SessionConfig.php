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
    public function __construct($id = null, array $fields = null, $action = null, $method = 'post')
    {
        $configs = \Sessions\Table\UserSessionConfigs::findAll();
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

        $fields = [
            [
                'submit' => [
                    'type'       => 'submit',
                    'value'      => 'Save',
                    'attributes' => [
                        'class'  => 'save-btn wide'
                    ]
                ],
                'role_id' => [
                    'type'       => 'select',
                    'label'      => 'Role',
                    'required'   => true,
                    'value'      => $roleValues,
                    'validators' => new Validator\NotEqual('----', 'Please select a role.')
                ],
                'log_type' => [
                    'type'       => 'select',
                    'label'      => 'Log Type',
                    'value'      => [
                        '--' => '----',
                        '1'  => 'Failure',
                        '2'  => 'Success',
                        '3'  => 'Both'
                    ]
                ],
                'multiple_sessions' => [
                    'type'      => 'radio',
                    'label'     => 'Multiple Sessions',
                    'value' => [
                        '1' => 'Yes',
                        '0' => 'No'
                    ],
                    'marked' => 1
                ],
                'timeout_warning' => [
                    'type'      => 'radio',
                    'label'     => 'Timeout Warning',
                    'value' => [
                        '1' => 'Yes',
                        '0' => 'No'
                    ],
                    'marked' => 0
                ]
            ],
            [
                'allowed_attempts' => [
                    'type'     => 'text',
                    'label'    => 'Allowed Attempts',
                    'attributes' => [
                        'size'    => 3
                    ],
                    'value'    => 0
                ],
                'session_expiration' => [
                    'type'     => 'text',
                    'label'    => 'Session Expiration (in minutes)',
                    'attributes' => [
                        'size'    => 3
                    ],
                    'value'    => 0
                ],
                'ip_allowed' => [
                    'type'     => 'textarea',
                    'label'    => 'IPs Allowed',
                    'attributes' => [
                        'rows' => 3,
                        'cols' => 60
                    ]
                ],
                'ip_blocked' => [
                    'type'     => 'textarea',
                    'label'    => 'IPs Blocked',
                    'attributes' => [
                        'rows' => 3,
                        'cols' => 60
                    ]
                ],
                'log_emails' => [
                    'type'     => 'textarea',
                    'label'    => 'Log Emails',
                    'attributes' => [
                        'rows' => 3,
                        'cols' => 60
                    ]
                ]
            ]
        ];

        parent::__construct($fields, $action, $method);

        $this->setAttribute('id', 'session-form');
        $this->setIndent('    ');
    }

}