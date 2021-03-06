<?php
defined('TYPO3_MODE') or die();

$tableName = \Fr\ApiToken\Domain\Model\Token::TABLE_NAME;

return [
    'ctrl' => [
        'title' => \Fr\ApiToken\Configuration\Localization::forTable($tableName),
        'label' => 'name',
        'sortby' => 'sorting',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'endtime' => 'valid_until',
        ],
        'typeicon_classes' => [
            'default' => $tableName,
        ],
        'searchFields' => 'name,identifier,description',
    ],
    'interface' => [
        'showRecordFieldList' => 'name,identifier,valid_until,description',
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => \Fr\ApiToken\Configuration\Localization::forCoreTranslation('visible'),
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],

        /**
         * Custom fields
         */
        'name' => [
            'label' => \Fr\ApiToken\Configuration\Localization::forField('name', $tableName),
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'max' => 255,
                'eval' => 'trim,required',
            ],
        ],
        'hash' => [
            'label' => \Fr\ApiToken\Configuration\Localization::forField('hash', $tableName),
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ],
        ],
        'identifier' => [
            'label' => \Fr\ApiToken\Configuration\Localization::forField('identifier', $tableName),
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'valid_until' => [
            'label' => \Fr\ApiToken\Configuration\Localization::forField('valid_until', $tableName),
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'readOnly' => false
            ]
        ],
        'description' => [
            'label' => \Fr\ApiToken\Configuration\Localization::forField('description', $tableName),
            'exclude' => 1,
            'config' => [
                'type' => 'text',
                'max' => 1024,
                'eval' => 'trim',
            ],
        ],

    ],
    'types' => [
        '0' => [
            'showitem' => '
                --div--;' . \Fr\ApiToken\Configuration\Localization::forTab('general', true) . ',
                    name,
                    identifier,
                    valid_until,
                    description,
                --div--;' . \Fr\ApiToken\Configuration\Localization::forTab('access', true) . ',
                    hidden,
                    --palette--;;access,
            ',
        ],
    ],
    'palettes' => [
        'access' => [
            'showitem' => '
                starttime, endtime
            ',
        ],
    ],
];
