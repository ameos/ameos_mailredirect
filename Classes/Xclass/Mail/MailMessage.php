<?php
namespace Ameos\AmeosMailredirect\Xclass\Mail;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;

/*
 * This file is part of the TYPO3 CMS project.
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

class MailMessage extends \TYPO3\CMS\Core\Mail\MailMessage
{

    /**
     * @var array original recipient
     */
    protected $originalRecipient = [];

    /**
     * @var array original CC addresses
     */
    protected $originalCc = [];

    /**
     * @var array original BCC addresses
     */
    protected $originalBcc = [];

    /**
     */
    private function initializeMailer()
    {
        $this->mailer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Mail\Mailer::class);
    }

    /**
     * Sends the message.
     *
     * @return int the number of recipients who were accepted for delivery
     */
    public function send()
    {
        if($this->isRedirectEnabled())
        {
            $this->updateTo();
            $this->updateCc();
            $this->updateBcc();
            $this->updateSubject();
        }
        if($this->isCopyEnabled())
        {
            $configuredBccs = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['copy.']['recipient'];
            // Must use setBcc before addBcc
            $this->setBcc(parent::getBcc());
            foreach(explode(';', $configuredBccs) as $newBcc)
            {
                $this->addBcc($newBcc);
            }
        }
        
        // Ensure to always have a From: header set
        if (empty($this->getFrom())) {
            $this->setFrom(MailUtility::getSystemFrom());
        }
        if(version_compare(TYPO3_branch, '9', '>='))
        {
            if (empty($this->getReplyTo())) {
                $replyTo = MailUtility::getSystemReplyTo();
                if (!empty($replyTo)) {
                    $this->setReplyTo($replyTo);
                }
            }
        }
        $this->initializeMailer();
        $this->sent = true;
        $this->getHeaders()->addTextHeader('X-Mailer', $this->mailerHeader);
        return $this->mailer->send($this, $this->failedRecipients);
    }

    /**
     * Update the To addresses of this message.
     *
     * @return void
     */
    public function updateTo()
    {
        $this->originalRecipient = parent::getTo();
        if (is_null($this->originalRecipient)) {
            $this->originalRecipient = [];
        }
        $addresses = [];
        $recipients = GeneralUtility::trimExplode(';', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['recipient']);
        foreach ($recipients as $recipient) {
            $addresses[$recipient] = $recipient;
        }
        if (!$this->_setHeaderFieldModel('To', $addresses)) {
            $this->getHeaders()->addMailboxHeader('To', $addresses);
        }
        
        parent::setTo($addresses);
    }

    /**
     * Update the CC addresses of this message.
     *
     * @return void
     */
    public function updateCc()
    {
        $this->originalCc = parent::getCc();
        if (is_null($this->originalCc)) {
            $this->originalCc = [];
        }
        parent::setCc([]);
    }

    /**
     * Update the BCC addresses of this message.
     *
     * @return void
     */
    public function updateBcc()
    {
        $this->originalBcc = parent::getBcc();
        if (is_null($this->originalBcc)) {
            $this->originalBcc = [];
        }
        parent::setBcc([]);
    }

    /**
     * Get the body content of this entity as a string.
     *
     * Returns NULL if no body has been set.
     *
     * @return string|null
     */
    public function getBody()
    {
        $body = parent::getBody();
        if($this->isRedirectEnabled())
        {
            $body .= '<br /><hr /><br />This mail must be sent';
            $body .= '<br/>as TO: ' . implode(';', array_keys($this->originalRecipient));
            $body .= '<br/>as CC: ' . implode(';', array_keys($this->originalCc));
            $body .= '<br/>as BCC: ' . implode(';', array_keys($this->originalBcc));
        }
        return $body;
    }
    
    /**
     * Update the subject of this message.
     *
     * @param string $subject
     *
     * @return void
     */
    public function updateSubject()
    {
        $subject = parent::getSubject();
        $prefix = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['subject_prefix'];
        if (trim($prefix) !== '') {
            $subject = trim($prefix) . ' ' . $subject;
        }
        parent::setSubject($subject);
    }

    /**
     * Check if redirection is enabled or not
     * 
     * @return bool
     */
    private function isRedirectEnabled()
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['activate'] == 1;
    }

    /**
     * Check if extra bcc is enabled or not
     * 
     * @return bool
     */
    private function isCopyEnabled()
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['copy.']['activate'] == 1;
    }
}
