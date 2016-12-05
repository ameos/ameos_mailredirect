<?php

$EM_CONF[$_EXTKEY] = array(
	'title'            => 'Mail redirect for debug',
	'description'      => 'This extension redirect all mail send with TYPO3 API to a debug address for developpement period.',
	'category'         => 'misc',
	'author'           => 'Ameos',
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
	'author_company'   => '',
	'version'          => '1.1.2',
	'constraints'      => array(
		'depends'      => array (
			'php' => '5.3.3-7.1.99',
			'typo3' => '6.2.0-7.99.99',
		),
		'conflicts' => array(),
		'suggests'  => array(),
	),
	'suggests' => array(),
);
