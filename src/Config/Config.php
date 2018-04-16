<?php

namespace Tworzenieweb\SqlProvisioner\Config;

use Dotenv\Dotenv;

class Config
{
    const MANDATORY_ENV_VARIABLES = [
        self::SMTP_HOST,
        self::EMAIL_SUBJECT,
        self::FROM_EMAIL,
    ];
    const SMTP_USER = 'SMTP_USER';
    const SMTP_PASSWORD = 'SMTP_PASSWORD';
    const SMTP_HOST = 'SMTP_HOST';
    const EMAIL_SUBJECT = 'EMAIL_SUBJECT';
    const FROM_EMAIL = 'FROM_EMAIL';
    const FROM_NAME = 'FROM_NAME';
    const TO_EMAILS = 'TO_EMAILS';
    const SERVER_HOST = 'SERVER_HOST';



    public function __construct(string $path)
    {
        $loader = new Dotenv($path);
        $loader->load();
        $loader->required(self::MANDATORY_ENV_VARIABLES)->notEmpty();
    }



    public function getSmtpUsername(): string
    {
        return $_ENV[self::SMTP_USER];
    }



    public function getSmtpPassword(): string
    {
        return $_ENV[self::SMTP_PASSWORD];
    }



    public function getSmtpHost(): string
    {
        return $_ENV[self::SMTP_HOST];
    }



    public function getEmailSubject(): string
    {
        return sprintf($_ENV[self::EMAIL_SUBJECT], $this->getServerHost());
    }



    public function getFromEmail(): string
    {
        return $_ENV[self::FROM_EMAIL];
    }



    public function getFromName(): string
    {
        return $_ENV[self::FROM_NAME];
    }



    public function getRecipientsList(): array
    {
        return explode(',', $_ENV[self::TO_EMAILS]);
    }



    public function getServerHost(): string
    {
        return $_ENV[self::SERVER_HOST];
    }
}
