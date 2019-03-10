<?php

namespace Tworzenieweb\SqlProvisioner\Config;

class EmailConfig
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

    /** @var bool  */
    private $enabled = true;

    /**
     * @return $this
     */
    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disable()
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled === true;
    }

    public function getSmtpUsername(): string
    {
        return getenv(self::SMTP_USER);
    }



    public function getSmtpPassword(): string
    {
        return getenv(self::SMTP_PASSWORD);
    }



    public function getSmtpHost(): string
    {
        return getenv(self::SMTP_HOST);
    }



    public function getEmailSubject(): string
    {
        return sprintf(getenv(self::EMAIL_SUBJECT), $this->getServerHost());
    }



    public function getFromEmail(): string
    {
        return getenv(self::FROM_EMAIL);
    }



    public function getFromName(): string
    {
        return getenv(self::FROM_NAME);
    }



    public function getRecipientsList(): array
    {
        return explode(',', getenv(self::TO_EMAILS));
    }



    public function getServerHost(): string
    {
        return getenv(self::SERVER_HOST);
    }
}
