<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Api Token',
    'description' => 'Generate and validate token auth for api requests.',
    'category' => 'fe',
    'state' => 'alpha',
    'author' => 'familie redlich:digital',
    'author_email' => 'a.maubach@familie-redlich.de',
    'author_company' => 'familie redlich digital',
    'version' => '0.7.0',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '10.4.21-10.4.99',
                ],
            'conflicts' =>
                [
                ],
            'suggests' =>
                [
                ],
        ]
];
