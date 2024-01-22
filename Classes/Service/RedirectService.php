<?php

declare(strict_types=1);

namespace Ameos\AmeosMailredirect\Service;

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RedirectService
{
    /**
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(private readonly ExtensionConfiguration $extensionConfiguration)
    {
        
    }

    /**
     * return true if redirection is enable
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->extensionConfiguration->get('ameos_mailredirect', 'activate') === true;
    }

    /**
     * return true if copy redirection is enable
     *
     * @return bool
     */
    public function isCopyEnabled(): bool
    {
        return (bool)$this->extensionConfiguration->get('ameos_mailredirect', 'copy/activate') === true;
    }

    /**
     * return subject prefix
     *
     * @return string
     */
    public function getSubjectPrefix(): string
    {
        return (string)$this->extensionConfiguration->get('ameos_mailredirect', 'subject_prefix');
    }

    /**
     * Returns recipients
     * 
     * @return array
     */
    public function getRecipients(): array
    {
        return array_map(
            fn ($address) => new Address($address),
            GeneralUtility::trimExplode(
                ';',
                $this->extensionConfiguration->get('ameos_mailredirect', 'recipient')
            )
        );
    }

    /**
     * Returns recipients for copy
     * 
     * @return array
     */
    public function getRecipientsForCopy(): array
    {
        return array_map(
            fn ($address) => new Address($address),
            GeneralUtility::trimExplode(
                ';',
                $this->extensionConfiguration->get('ameos_mailredirect', 'copy/recipient')
            )
        );
    }

    /**
     * convert symfony address to raw text
     *
     * @param array $addresses
     * @return array
     */
    public function symfonyToAdress(array $addresses): array
    {
        return array_map(fn (Address $address) => $address->getAddress(), $addresses);
    }
}