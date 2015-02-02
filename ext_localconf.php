<?php
if (!defined ('TYPO3_MODE')) die('Access denied.');

// unserialize configuration
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect'] = unserialize($_EXTCONF);

// xclass
if($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['activate'] == 1) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['\\TYPO3\\CMS\\Core\\Mail\\MailMessage'] = array('className' => 'Ameos\\AmeosMailredirect\\Xclass\\Mail\\MailMessage');
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Mail\\MailMessage'] = array('className' => 'Ameos\\AmeosMailredirect\\Xclass\\Mail\\MailMessage');
}



