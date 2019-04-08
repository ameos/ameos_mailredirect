<?php
namespace Ameos\AmeosMailredirect\Xclass\Mail;

use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * Get the To addresses of this message.
     *
     * @return array
     */
    public function getTo()
    {
        $this->originalRecipient = parent::getTo();
        if (is_null($this->originalRecipient)) {
            $this->originalRecipient = [];
        }
        $addresses = [];
        $recipients = GeneralUtility::trimExplode(';', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['recipient']);
        foreach ($recipients as $recipient) {
            $addresses[$recipient] = '';
        }
        if (!$this->_setHeaderFieldModel('To', $addresses)) {
            $this->getHeaders()->addMailboxHeader('To', $addresses);
        }
        return $addresses;
    }

    /**
     * Get the CC addresses of this message.
     *
     * @return array
     */
    public function getCc()
    {
        $this->originalCc = parent::getCc();
        if (is_null($this->originalCc)) {
            $this->originalCc = [];
        }
        return [];
    }

    /**
     * Get the BCC addresses of this message.
     *
     * @return array
     */
    public function getBcc()
    {
        $this->originalBcc = parent::getBcc();
        if (is_null($this->originalBcc)) {
            $this->originalBcc = [];
        }
        return [];
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
        $body .= '<br /><hr /><br />This mail must be sent';
        $body .= '<br/>as TO: ' . implode(';', array_keys($this->originalRecipient));
        $body .= '<br/>as CC: ' . implode(';', array_keys($this->originalCc));
        $body .= '<br/>as BCC: ' . implode(';', array_keys($this->originalBcc));
        return $body;
    }

    /**
     * Set the subject of this message.
     *
     * @param string $subject
     *
     * @return Ameos\AmeosMailredirect\Xclass\Mail\MailMessage
     */
    public function setSubject($subject)
    {
        $prefix = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['subject_prefix'];
        if (trim($prefix) !== '') {
            $subject = trim($prefix) . ' ' . $subject;
        }
        parent::setSubject($subject);
        return $this;
    }
}
