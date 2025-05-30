<?php

/*
 * This file is part of the api_token Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

defined('TYPO3') or die();

$tableName = \CPSIT\ApiToken\Domain\Model\Token::TABLE_NAME;

return [
    'ctrl' => [
        'title' => \CPSIT\ApiToken\Configuration\Localization::forTable($tableName),
        'label' => 'name',
        'sortby' => 'sorting',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
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
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => \CPSIT\ApiToken\Configuration\Localization::forCoreTranslation('visible'),
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],

        /**
         * Custom fields
         */
        'name' => [
            'label' => \CPSIT\ApiToken\Configuration\Localization::forField('name', $tableName),
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'max' => 255,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'hash' => [
            'label' => \CPSIT\ApiToken\Configuration\Localization::forField('hash', $tableName),
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'identifier' => [
            'label' => \CPSIT\ApiToken\Configuration\Localization::forField('identifier', $tableName),
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'valid_until' => [
            'label' => \CPSIT\ApiToken\Configuration\Localization::forField('valid_until', $tableName),
            'exclude' => 1,
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'readOnly' => false,
            ],
        ],
        'description' => [
            'label' => \CPSIT\ApiToken\Configuration\Localization::forField('description', $tableName),
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
                --div--;' . \CPSIT\ApiToken\Configuration\Localization::forTab('general', true) . ',
                    name,
                    identifier,
                    valid_until,
                    description,
                --div--;' . \CPSIT\ApiToken\Configuration\Localization::forTab('access', true) . ',
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
