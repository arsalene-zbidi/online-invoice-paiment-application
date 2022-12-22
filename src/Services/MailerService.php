<?php

namespace App\Services;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService

{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function sendEmail($to="arsalenezbidibac@gmail.com",$content="<p>See Twig integration for better HTML integration!</p>"): void
    {
        $email = (new Email())
            ->from('verifpython@gmail.com')
            ->to($to)

            ->subject('Paiment de facture avec MyBna ')
            ->html($content);


        $this->mailer->send($email);

        // ...
    }

}