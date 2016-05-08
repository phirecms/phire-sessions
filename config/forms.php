<?php

return [
    'Phire\Sessions\Form\SessionConfig' => [
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
                'value'      => null,
                'validators' => new \Pop\Validator\NotEqual('----', 'Please select a role.')
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
                    'rows' => 5,
                    'cols' => 60
                ]
            ],
            'ip_blocked' => [
                'type'     => 'textarea',
                'label'    => 'IPs Blocked',
                'attributes' => [
                    'rows' => 5,
                    'cols' => 60
                ]
            ],
            'log_emails' => [
                'type'     => 'textarea',
                'label'    => 'Log Emails',
                'attributes' => [
                    'rows' => 5,
                    'cols' => 60
                ]
            ]
        ]
    ]
];