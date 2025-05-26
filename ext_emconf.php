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

$EM_CONF[$_EXTKEY] = [
    'title' => 'Api Token',
    'description' => 'Generate and validate token auth for api requests.',
    'category' => 'fe',
    'state' => 'alpha',
    'author' => 'familie redlich:digital',
    'author_email' => 'a.maubach@familie-redlich.de',
    'author_company' => 'familie redlich digital',
    'version' => '0.9.6',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '12.4.0-13.4.99',
                ],
            'conflicts' =>
                [
                ],
            'suggests' =>
                [
                ],
        ],
];
