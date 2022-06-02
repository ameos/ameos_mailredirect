<?php
if (!defined ('TYPO3_MODE')) die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect'] =
    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )
    ->get('ameos_mailredirect');

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Mail\Mailer::class] = array(
    'className' => \Ameos\AmeosMailredirect\Xclass\Mail\Mailer::class,
);