<?php

namespace spec\Tworzenieweb\SqlProvisioner\Service;

use PhpSpec\ObjectBehavior;
use Swift_Mailer;

class MailerSpec extends ObjectBehavior
{
    function let(Swift_Mailer $mailer)
    {
        $this->beConstructedWith($mailer);
    }

    function it_should_send_email(\Swift_Message $message) {
        $this->send($message);
    }
}
