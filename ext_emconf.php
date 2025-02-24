<?php

$EM_CONF['ameos_mailredirect'] = [
    'title'            => 'Mail redirect for debug',
    'description'      => 'This extension redirect all mail send with TYPO3 API to a debug address for developpement period.',
    'category'         => 'misc',
    'author'           => 'Ameos Team',
    'author_email'     => 'typo3dev@ameos.com',
    'shy'              => '',
    'dependencies'     => '',
    'conflicts'        => '',
    'priority'         => '',
    'module'           => '',
    'state'            => 'stable',
    'internal'         => '',
    'uploadfolder'     => 0,
    'createDirs'       => '',
    'modify_tables'    => '',
    'clearCacheOnLoad' => 0,
    'lockType'         => '',
    'author_company'   => 'Ameos',
    'version'          => '3.1.2',
    'constraints'      => [
        'depends'      => [
            'typo3' => '12.4.0-13.4.99',
            'php'   => '8.0.0-8.3.99'
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
