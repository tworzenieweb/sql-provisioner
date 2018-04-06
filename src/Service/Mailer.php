<?php

namespace Tworzenieweb\SqlProvisioner\Service;

use Swift_Mailer;
use Swift_Message;

class Mailer
{
    /** @var Swift_Mailer  */
    private $mailer;


    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }


    public function send(Swift_Message $message): bool
    {
        $numberOfEmailsSent = $this->mailer->send($message);

        return $numberOfEmailsSent > 0;
    }
}
