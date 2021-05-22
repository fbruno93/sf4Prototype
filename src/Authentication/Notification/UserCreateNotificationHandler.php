<?php

namespace App\Authentication\Notification;

use App\Authentication\Model\Repository\UserValidationRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Email;

class UserCreateNotificationHandler implements MessageHandlerInterface
{
    private MailerInterface $mailer;

    private UserValidationRepository $userValidationRepository;

    public function __construct(MailerInterface $mailer, UserValidationRepository $userValidationRepository)
    {
        $this->mailer = $mailer;
        $this->userValidationRepository = $userValidationRepository;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function __invoke(UserCreateNotification $message)
    {
        $userValidation = $this->userValidationRepository->findOneBy(['user' => $message->getUserId() ]);
        $email = (new Email())
            ->from('ferrybrunom@gmail.com')
            ->to('fbruno@hotmail.fr')
            ->subject('Welcome !')
            ->html("<p>Please <strong>validate</strong> your email</p> {$userValidation->getHash()}");

        $this->mailer->send($email);
    }
}
