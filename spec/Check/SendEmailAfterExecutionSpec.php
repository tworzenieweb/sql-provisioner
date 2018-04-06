<?php

namespace spec\Tworzenieweb\SqlProvisioner\Check;

use Tworzenieweb\SqlProvisioner\Check\CheckInterface;
use Tworzenieweb\SqlProvisioner\Check\SendEmailAfterExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tworzenieweb\SqlProvisioner\Config\Config;
use Tworzenieweb\SqlProvisioner\Database\Connection;
use Tworzenieweb\SqlProvisioner\Model\Candidate;
use Tworzenieweb\SqlProvisioner\Service\Mailer;
use Tworzenieweb\SqlProvisioner\View\View;

/**
 * @package spec\Tworzenieweb\SqlProvisioner\Check
 * @mixin SendEmailAfterExecution
 */
class SendEmailAfterExecutionSpec extends ObjectBehavior
{
    const MAIL_SUBJECT = '[SQL PROVISIONER] Database Query applied at %s server';
    const FROM_EMAIL = 'sqlprovisioning@jobleads.de';
    const FROM_NAME = 'SQL Provisioning';
    const RECIPIENTS_LIST = ['foo.bar@baz.com' => 'foo.bar@baz.com', 'bar.foo@baz.com' => 'foo.bar@baz.com'];
    const SERVER_ADDRESS = 'server01';



    function let(Mailer $mailer, Config $config, View $view, Connection $connection)
    {
        $this->beConstructedWith($mailer, $config, $view, $connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SendEmailAfterExecution::class);
        $this->shouldImplement(CheckInterface::class);
    }

    function it_should_send_email(Candidate $candidate, Config $config, Mailer $mailer, Connection $connection, View $view)
    {
        $candidate->getContent()->willReturn('DROP TABLE foo;');
        $candidate->getName()->willReturn('foo.sql');
        $view->render(Argument::type('array'))->willReturn('foo template');
        $config->getEmailSubject()->willReturn(self::MAIL_SUBJECT);
        $config->getFromEmail()->willReturn(self::FROM_EMAIL);
        $config->getFromName()->willReturn(self::FROM_NAME);
        $config->getRecipientsList()->willReturn(self::RECIPIENTS_LIST);
        $config->getServerHost()->willReturn(self::SERVER_ADDRESS);
        $connection->getDatabaseName()->willReturn('foo');
        $mailer->send(Argument::that(function (\Swift_Message $inputMessage) {
            return $inputMessage->getFrom() == [self::FROM_EMAIL => self::FROM_NAME] &&
                $inputMessage->getTo() == self::RECIPIENTS_LIST &&
                $inputMessage->getSubject() == self::MAIL_SUBJECT;
        }))->shouldBeCalled();
        $this->execute($candidate);
    }
}
