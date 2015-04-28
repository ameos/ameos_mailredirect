<?php
namespace Ameos\AmeosMailredirect\Xclass\Mail;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailMessage extends \TYPO3\CMS\Core\Mail\MailMessage {

	/**
	 * @var array original recipient
	 */ 
	protected $originalRecipient;

	/**
     * Get the To addresses of this message.
     *
     * @return array
     */
    public function getTo()
    {
		$this->originalRecipient = parent::getTo();
		$addresses = array();
		$recipients = GeneralUtility::trimExplode(';', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['recipient']);
		foreach($recipients as $recipient) {
			$addresses[$recipient] = '';
		}
		if (!$this->_setHeaderFieldModel('To', $addresses)) {
            $this->getHeaders()->addMailboxHeader('To', $addresses);
        }
        return $addresses;
    }

    /**
     * Get the body content of this entity as a string.
     *
     * Returns NULL if no body has been set.
     *
     * @return string|null
     */
    public function getBody() {
		$body = parent::getBody();
		$body.= '<br /><hr /><br />This mail must be sent to : ' . implode(';', array_keys($this->originalRecipient));
		return $body;
	}

    

    /**
     * Set the subject of this message.
     *
     * @param string $subject
     *
     * @return Ameos\AmeosMailredirect\Xclass\Mail\MailMessage
     */
    public function setSubject($subject) {
		$prefix = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_mailredirect']['subject_prefix'];
		if(trim($prefix) !== '') {
			$subject = trim($prefix) . ' ' . $subject;
		}
		parent::setSubject($subject);
		return $this;
	}
}
