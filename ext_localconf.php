<?php
if (!defined ('TYPO3_MODE')) die('Access denied.');

// get configuration
if(version_compare(TYPO3_branch, '10', '>='))
{
    $mailMessageClassName = 'Ameos\\AmeosMailredirect\\Xclass\\Mail\\SymfonyMailMessage';
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect'] = 
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )
        ->get('ameos_mailredirect');
}
else
{
    $mailMessageClassName = 'Ameos\\AmeosMailredirect\\Xclass\\Mail\\MailMessage';
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect'] = unserialize($_EXTCONF);
}

// xclass
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['\\TYPO3\\CMS\\Core\\Mail\\MailMessage'] = array('className' => $mailMessageClassName);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Mail\\MailMessage'] = array('className' => $mailMessageClassName);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['t3lib_mail_Message'] = array('className' => $mailMessageClassName);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['\\t3lib_mail_Message'] = array('className' => $mailMessageClassName);