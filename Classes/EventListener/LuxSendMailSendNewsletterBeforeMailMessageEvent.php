<?php

declare(strict_types=1);

namespace Ameos\AmeosMailredirect\EventListener;

use Ameos\AmeosMailredirect\Service\RedirectService;
use In2code\Luxletter\Events\SendMailSendNewsletterBeforeMailMessageEvent;
use In2code\Luxletter\Mail\MailMessage;

class LuxSendMailSendNewsletterBeforeMailMessageEvent
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
     * @param SendMailSendNewsletterBeforeEvent $event
     * @return void
     */
    public function __invoke(SendMailSendNewsletterBeforeMailMessageEvent $event): void
    {
        /** @var MailMessage */
        $message = $event->getMailMessage();
        if (is_a($message, MailMessage::class)) {
            $prefixSubject = $this->redirectService->getSubjectPrefix();
            
            if ($prefixSubject !== '') {
                $message->setSubject($prefixSubject . ' ' . $message->getSubject());
            }

            if ($this->redirectService->isEnabled()) {
                $originalRecipients = array_keys($event->getReceiver());                
                
                $message->setTo(
                    $this->redirectService->getRecipientsRaw()
                );

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
                $originalRecipientsCopy = $this->redirectService->symfonyToAdress($message->getCc());

                $message->html(
                    sprintf(
                        '%s<br />Cc : %s',
                        $message->getHtmlBody(),
                        implode(';', $originalRecipientsCopy)
                    )
                );
                $message->text(
                    sprintf(
                        '%s%sCc : %s',
                        $message->getTextBody(),
                        chr(10),
                        implode(';', $originalRecipientsCopy)
                    )
                );
                $message->cc(...$this->redirectService->getRecipientsForCopy());
            }
        }
    }
}