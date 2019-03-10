<?php

namespace Tworzenieweb\SqlProvisioner\Config;

use Dotenv\Dotenv;

/**
 * Class ProvisionConfig
 *
 * @package Tworzenieweb\SqlProvisioner\Config
 */
class ProvisionConfig
{
    /**
     * @var string
     */
    private $envPath;

    /**
     * @var bool
     */
    private $force = false;

    /**
     * @var bool
     */
    private $skipProvisioned = false;

    /**
     * @var bool
     */
    private $skipSyntaxCheck = false;

    /**
     * @var EmailConfig
     */
    private $emailConfig;

    /**
     * ProvisionConfig constructor.
     *
     * @param string      $envPath
     * @param EmailConfig $emailConfig
     */
    public function __construct(string $envPath, EmailConfig $emailConfig)
    {
        $this->envPath     = $envPath;
        $this->emailConfig = $emailConfig;
    }

    /**
     * @param string $envPath
     *
     * @return ProvisionConfig
     */
    public function withEnvPath(string $envPath): ProvisionConfig
    {
        $this->envPath = $envPath;

        return $this;
    }

    /**
     * @param bool $force
     *
     * @return ProvisionConfig
     */
    public function force(bool $force = true): ProvisionConfig
    {
        $this->force = $force;

        return $this;
    }

    /**
     * @param bool $skipProvisioned
     *
     * @return ProvisionConfig
     */
    public function skipProvisioned(bool $skipProvisioned = true): ProvisionConfig
    {
        $this->skipProvisioned = $skipProvisioned;

        return $this;
    }

    /**
     * @param bool $skipSyntaxCheck
     *
     * @return ProvisionConfig
     */
    public function skipSyntaxCheck(bool $skipSyntaxCheck = true): ProvisionConfig
    {
        $this->skipSyntaxCheck = $skipSyntaxCheck;

        return $this;
    }

    /**
     * @return ProvisionConfig
     */
    public function sendEmail(): ProvisionConfig
    {
        $this->emailConfig->enable();

        return $this;
    }

    /**
     * @return ProvisionConfig
     */
    public function skipEmail(): ProvisionConfig
    {
        $this->emailConfig->disable();

        return $this;
    }

    /**
     * @return void
     */
    public function load()
    {
        $loader = $this->composeDotenv();
        $loader->load();

        if ($this->sendEmail === true) {
            $loader->required(EmailConfig::MANDATORY_ENV_VARIABLES)->notEmpty();
        }
    }

    /**
     * @return string
     */
    public function getEnvPath(): string
    {
        return $this->envPath;
    }

    /**
     * @return bool
     */
    public function isForce(): bool
    {
        return $this->force;
    }

    /**
     * @return bool
     */
    public function isSkipProvisioned(): bool
    {
        return $this->skipProvisioned;
    }

    /**
     * @return bool
     */
    public function isSkipSyntaxCheck(): bool
    {
        return $this->skipSyntaxCheck;
    }

    /**
     * @return bool
     */
    public function isSendEmail(): bool
    {
        return $this->sendEmail;
    }

    /**
     * @return Dotenv
     */
    protected function composeDotenv(): Dotenv
    {
        if (!is_readable($this->envPath)) {
            throw new \RuntimeException(
                sprintf('Env file path [%s] is not readable', $this->envPath)
            );
        }

        if (is_dir($this->envPath)) {
            return new Dotenv($this->envPath);
        }

        if (is_file($this->envPath)) {
            return new Dotenv(dirname($this->envPath), basename($this->envPath));
        }

        throw new \RuntimeException(
            sprintf('Could not define whether provided env path [%s] is a directory or file', $this->envPath)
        );
    }
}
