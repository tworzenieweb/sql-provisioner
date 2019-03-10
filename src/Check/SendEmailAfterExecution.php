<?php

namespace Tworzenieweb\SqlProvisioner\Check;

use Swift_Message;
use Tworzenieweb\SqlProvisioner\Config\EmailConfig;
use Tworzenieweb\SqlProvisioner\Database\Connection;
use Tworzenieweb\SqlProvisioner\Model\Candidate;
use Tworzenieweb\SqlProvisioner\Service\Mailer;
use Tworzenieweb\SqlProvisioner\View\View;

class SendEmailAfterExecution implements CheckInterface
{
    /** @var Mailer */
    private $mailer;

    /** @var EmailConfig */
    private $config;

    /** @var View */
    private $view;

    /** @var Connection */
    private $connection;



    public function __construct(Mailer $mailer, EmailConfig $config, View $view, Connection $connection)
    {
        $this->mailer = $mailer;
        $this->config = $config;
        $this->view = $view;
        $this->connection = $connection;
    }



    public function execute(Candidate $candidate): bool
    {
        if (!$this->config->isEnabled()) {
            return true;
        }

        $message = $this->composeMessage($candidate);
        $mailsSent = $this->mailer->send($message);

        return $mailsSent > 0;
    }



    /**
     * @inheritDoc
     */
    public function getErrorCode(): string
    {
        return '';
    }



    /**
     * @inheritDoc
     */
    public function getLastErrorMessage(): string
    {
        return '';
    }



    private function composeMessage(Candidate $candidate): Swift_Message
    {
        $subject = $this->config->getEmailSubject();
        $fromEmail = $this->config->getFromEmail();
        $fromName = $this->config->getFromName();
        $toEmails = $this->config->getRecipientsList();
        $serverHost = $this->config->getServerHost();
        $sql = $candidate->getContent();
        $filename = $candidate->getName();
        $message = new Swift_Message($subject);
        $message->setFrom([$fromEmail => $fromName]);
        $message->setTo($toEmails);
        $dbName = $this->connection->getDatabaseName();
        $body = $this->view->render(['serverHost' => $serverHost, 'sql' => $sql, 'filename' => $filename, 'dbName' => $dbName]);
        $message->setBody($body, 'text/html');

        return $message;
    }
}
