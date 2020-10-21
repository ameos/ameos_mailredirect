<?php
namespace Ameos\AmeosMailredirect\Xclass\Mail;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Mail\Mailer;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Address;

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

class SymfonyMailMessage extends \TYPO3\CMS\Core\Mail\MailMessage
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

    private function initializeMailer(): void
    {
        $this->mailer = GeneralUtility::makeInstance(Mailer::class);
    }

    /**
     * Sends the message.
     *
     * This is a short-hand method. It is however more useful to create
     * a Mailer instance which can be used via Mailer->send($message);
     *
     * @return bool whether the message was accepted or not
     */
    public function send(): bool
    {
        $this->initializeMailer();
        $this->sent = false;

        if($this->isRedirectEnabled())
        {
            $this->updateTo();
            $this->updateCc();
            $this->updateBcc();
            $this->updateSubject();
        }
        if($this->isCopyEnabled())
        {
            $configuredBccs = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['copy']['recipient'];
            // Must use setBcc before addBcc
            $this->setBcc(parent::getBcc());
            foreach(explode(';', $configuredBccs) as $newBcc)
            {
                $this->addBcc(GeneralUtility::makeInstance(Address::class, $newBcc));
            }
        }

        $this->mailer->send($this);
        $sentMessage = $this->mailer->getSentMessage();
        if ($sentMessage) {
            $this->sent = true;
        }
        return $this->sent;
    }

    /**
     * Update the to addresses of this message.
     *
     * @return void
     */
    public function updateTo(): void
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

        parent::setTo($addresses);
    }

    /**
     * Update the CC addresses of this message.
     *
     * @return void
     */
    public function updateCc(): void
    {
        $this->orignalCc = parent::getCc();
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
    public function updateBcc(): void
    {
        $this->originalBcc = parent::getBcc();
        if (is_null($this->originalBcc)) {
            $this->originalBcc = [];
        }
        parent::setBcc([]);
    }

    /**
     * Get and Update the body content of this entity as a TextPart.
     *
     * Returns NULL if no body has been set.
     *
     * @return string|null
     */
    public function getBody(): AbstractPart
    {
        if($this->isRedirectEnabled())
        {
            $textPart = parent::getBody();
            $bodyText = $textPart->bodyToString();
            $bodyText .= '<br /><hr /><br />This mail must be sent';
            $bodyText .= '<br/>as TO: ' . $this->parseSymfonyAddressesToString($this->originalRecipient);
            $bodyText .= '<br/>as CC: ' . $this->parseSymfonyAddressesToString($this->originalCc);
            $bodyText .= '<br/>as BCC: ' . $this->parseSymfonyAddressesToString($this->originalBcc);

            $charset = 'utf-8';
            if($textPart->getPreparedHeaders()->has('content-type'))
            {
                if(array_key_exists('charset', $textPart->getPreparedHeaders()->get('content-type')->getParameters()))
                {
                    $charset = $textPart->getPreparedHeaders()->get('content-type')->getParameters()['charset'];
                }
            }
            $encoding = null;
            if($textPart->getPreparedHeaders()->has('content-transfer-encoding'))
            {
                $encoding = $textPart->getPreparedHeaders()->get('content-transfer-encoding')->getValue();
            }

            $newTextPart = GeneralUtility::makeInstance(\Symfony\Component\Mime\Part\TextPart::class, $bodyText, $charset, $textPart->getMediaSubtype(), $encoding);

            return $newTextPart;
        }
        else
        {
            return parent::getBody();
        }
    }

    /**
     * Update the subject of this message.
     *
     * @param string $subject
     *
     * @return Ameos\AmeosMailredirect\Xclass\Mail\SymfonyMailMessage
     */
    public function updateSubject(): void
    {
        $subject = parent::getSubject();
        $prefix = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['subject_prefix'];
        if (trim($prefix) !== '') {
            $subject = trim($prefix) . ' ' . $subject;
        }
        parent::setSubject($subject);
    }

    /**
     * Parse symfony addresses to a human readable text
     * 
     * @param Address[] $addresses
     * 
     * @return string
     */
    private function parseSymfonyAddressesToString($addresses): string
    {
        $parsedAddresses = [];

        foreach($addresses as $address)
            $parsedAddresses[] = $address->getAddress();

        return implode(';', $parsedAddresses);
    }

    /**
     * Check if redirection is enabled or not
     * 
     * @return bool
     */
    private function isRedirectEnabled(): bool
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['activate'] == 1;
    }

    /**
     * Check if extra bcc is enabled or not
     * 
     * @return bool
     */
    private function isCopyEnabled(): bool
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['copy']['activate'] == 1;
    }
}
