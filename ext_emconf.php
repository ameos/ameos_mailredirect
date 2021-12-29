<?php

$EM_CONF[$_EXTKEY] = [
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
    'version'          => '1.2.2',
    'constraints'      => [
        'depends'      => [
            'typo3' => '8.7.0-11.5.99',
            'php'   => '5.5.0-7.3.99'
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
