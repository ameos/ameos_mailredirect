<?php

declare(strict_types=1);

namespace Ameos\AmeosMailredirect\EventListener;

use Ameos\AmeosMailredirect\Service\RedirectService;
use Symfony\Component\Mime\Email;
use TYPO3\CMS\Core\Mail\Event\BeforeMailerSentMessageEvent;

class BeforeMailerSentMessage
{
    /**
     * @param RedirectService $redirectService
     */
    public function __construct(private readonly RedirectService $redirectService)
    {
        
    }

    /**
     * event before mailer sent message
     * redirect mail if redirection is enable
     *
     * @param BeforeMailerSentMessageEvent $event
     * @return void
     */
    public function __invoke(BeforeMailerSentMessageEvent $event): void
    {
        /** @var Email */
        $message = $event->getMessage();
        if (is_a($message, Email::class)) {
            $prefixSubject = $this->redirectService->getSubjectPrefix();
            if ($prefixSubject !== '') {
                $message->subject($prefixSubject . ' ' . $message->getSubject());
            }

            if ($this->redirectService->isEnabled()) {
                $originalRecipients = $this->redirectService->symfonyToAdress($message->getTo());
                $message->to(...$this->redirectService->getRecipients());
                $message->html(
                    sprintf(
                        '%s<br />To : %s',
                        $message->getHtmlBody(),
                        implode(';', $originalRecipients)
                    )
                );
                $message->text(
                    sprintf(
                        '%s%sTo : %s',
                        $message->getTextBody(),
                        chr(10),
                        implode(';', $originalRecipients)
                    )
                );
            }

            if ($this->redirectService->isCopyEnabled()) {
                $originalRecipients = $this->redirectService->symfonyToAdress($message->getCc());
                $message->cc(...$this->redirectService->getRecipientsForCopy());
                $message->html(
                    sprintf(
                        '%s<br />Cc : %s',
                        $message->getHtmlBody(),
                        implode(';', $originalRecipients)
                    )
                );
                $message->text(
                    sprintf(
                        '%s%sCc : %s',
                        $message->getTextBody(),
                        chr(10),
                        implode(';', $originalRecipients)
                    )
                );
            }
        }
    }
}