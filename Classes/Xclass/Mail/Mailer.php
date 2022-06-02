<?php

namespace Ameos\AmeosMailredirect\Xclass\Mail;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Symfony\Component\Mime\Part\TextPart;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Mailer extends \TYPO3\CMS\Core\Mail\Mailer
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
     * @inheritdoc
     */
    public function send(RawMessage $message, Envelope $envelope = null): void
    {
        if (true === $this->isRedirectEnabled()) {
            $this->originalRecipient = $message->getTo();
            $this->originalCc = $message->getCc();
            $this->originalBcc = $message->getBcc();

            $originalTo = [];
            foreach ($this->originalRecipient as $recipient) {
                if (is_a($recipient, Address::class)) {
                    $originalTo[] = $recipient->getAddress();
                } else {
                    $originalTo[] = $recipient;
                }
            }

            $message->html($this->getUpdatedHtmlBody($message->getHtmlBody()));
            $message->text($this->getUpdatedTextBody($message->getTextBody()));

            $newRecipients = $this->getNewRecipients();
            $message->to(...$newRecipients);
            if (!empty($this->originalCc)) {
                $message->cc(...$newRecipients);
            }
            if (!empty($this->originalBcc)) {
                $message->bcc(...$newRecipients);
            }
            $message->subject($this->getSubjectPrefix() . $message->getSubject());
        } elseif(true === $this->isCopyEnabled()) {
            $message->addBcc(...$this->getCopyRecipients());
        }

        parent::send($message, $envelope);
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
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['copy']['activate'] == 1;
    }

    /**
     * Returns news recipients configured in ext
     * 
     * @return array
     */
    private function getNewRecipients()
    {
        $addresses = [];
        $recipients = GeneralUtility::trimExplode(
            ';',
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['recipient']
        );
        foreach ($recipients as $recipient) {
            $addresses[] = $recipient;
        }
        return $addresses;
    }

    /**
     * Returns copy recipients configured in ext
     * 
     * @return array
     */
    private function getCopyRecipients()
    {
        $addresses = [];
        $recipients = GeneralUtility::trimExplode(
            ';',
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['copy']['recipient']
        );
        foreach ($recipients as $recipient) {
            $addresses[] = $recipient;
        }
        return $addresses;
    }

    /**
     * Returns subject prefix from configuration
     * 
     * @return string
     */
    private function getSubjectPrefix()
    {
        return (string)$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['subject_prefix'];
    }

    private function getUpdatedBody(AbstractPart $abstractPart): AbstractPart
    {
        if(get_class($abstractPart) === TextPart::class) {
            $abstractPart = $this->updateTextPart($abstractPart);
        } elseif(
            get_class($abstractPart) === MixedPart::class
            || get_class($abstractPart) === AlternativePart::class
        ) {
            $parts = $abstractPart->getParts();
            $firstPart = current($parts);
            $firstPart = $this->updateTextPart($firstPart);
            $parts[0] = $firstPart;
            $abstractPart = GeneralUtility::makeInstance(get_class($abstractPart), ...$parts);
        }
        return $abstractPart;
    }

    private function getUpdatedTextBody(?string $text)
    {
        return $text . $this->getSendToSuffix('text');
    }

    private function getUpdatedHtmlBody(?string $html)
    {
        return $html . $this->getSendToSuffix();
    }

    private function updateTextPart(TextPart $part): TextPart
    {
        $bodyText = $part->getBody();
        $bodyText .= $this->getSendToSuffix($part->getMediaSubtype());

        $charset = 'utf-8';

        if($part->getPreparedHeaders()->has('content-type'))
        {
            if(array_key_exists('charset', $part->getPreparedHeaders()->get('content-type')->getParameters()))
            {
                $charset = $part->getPreparedHeaders()->get('content-type')->getParameters()['charset'];
            }
        }
        $encoding = null;
        if($part->getPreparedHeaders()->has('content-transfer-encoding'))
        {
            $encoding = $part->getPreparedHeaders()->get('content-transfer-encoding')->getValue();
        }

        return GeneralUtility::makeInstance(TextPart::class, $bodyText, $charset, $part->getMediaSubtype(), $encoding);
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

        foreach ($addresses as $address) {
            $parsedAddresses[] = $address->getAddress();
        }

        return implode(';', $parsedAddresses);
    }

    private function getSendToSuffix(string $format = 'html')
    {
        $suffix = '';
        if ($format === 'text' || $format === 'plain') {
            $lineBreak = PHP_EOL;
        } else {
            $lineBreak = '<br>';
            $suffix .= $lineBreak . '<hr />';
        }

        $suffix .= $lineBreak . 'This mail must be sent';
        $suffix .= $lineBreak . 'as TO: ' . $this->parseSymfonyAddressesToString($this->originalRecipient);
        $suffix .= $lineBreak . 'as CC: ' . $this->parseSymfonyAddressesToString($this->originalCc);
        $suffix .= $lineBreak . 'as BCC: ' . $this->parseSymfonyAddressesToString($this->originalBcc);

        return $suffix;
    }
}