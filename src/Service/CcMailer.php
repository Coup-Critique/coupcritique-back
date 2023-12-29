<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Twig\Environment;

class CcMailer
{
    private ?DkimSigner $signer;
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct($projectDir, MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $keyPath = $projectDir . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "dkim" . DIRECTORY_SEPARATOR . "private.pem";
        // for ci actions containers
        if (file_exists($keyPath)) {
            $this->signer = new DkimSigner("file://" . $keyPath, 'coupcritique.fr', 'ovh0622');
        } else {
            $this->signer = null;
        }
    }

    public function send(Email $email)
    {
        if (!is_null($this->signer)) {
            if ($email instanceof TemplatedEmail) {
                $email->html($this->twig->render(
                    $email->getHtmlTemplate(),
                    $email->getContext()
                ));
            }
            // $email = new Message($email->getPreparedHeaders(), $email->getBody());
            $this->signer->sign($email);
        }
        $this->mailer->send($email);
        return $email;
    }
}
