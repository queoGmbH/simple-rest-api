<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Simple REST API',
    'description' => 'A simple REST API framework for TYPO3 — create endpoints via PHP attributes.',
    'category' => 'misc',
    'author' => 'Sebastian Hofer',
    'author_email' => 's.hofer@queo-group.com',
    'author_company' => 'Queo Group',
    'state' => 'stable',
    'version' => '0.3.1',
    'constraints' => [
        'depends' => [
            'php' => '8.2.0-0.0.0',
            'typo3' => '13.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
