<?php
declare(strict_types=1);

namespace GibsonOS\Module\Zeus\Dto;

class InexogyConsumer
{
    private ?string $key = null;

    private ?string $secret = null;

    private ?string $requestToken = null;

    private ?string $requestTokenSecret = null;

    private ?string $accessToken = null;

    private ?string $accessTokenSecret = null;

    private ?string $verifier = null;

    public function __construct(
        private readonly string $email,
        private readonly string $password,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $key): InexogyConsumer
    {
        $this->key = $key;

        return $this;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): InexogyConsumer
    {
        $this->secret = $secret;

        return $this;
    }

    public function getRequestToken(): ?string
    {
        return $this->requestToken;
    }

    public function setRequestToken(?string $requestToken): InexogyConsumer
    {
        $this->requestToken = $requestToken;

        return $this;
    }

    public function getVerifier(): ?string
    {
        return $this->verifier;
    }

    public function setVerifier(?string $verifier): InexogyConsumer
    {
        $this->verifier = $verifier;

        return $this;
    }

    public function getRequestTokenSecret(): ?string
    {
        return $this->requestTokenSecret;
    }

    public function setRequestTokenSecret(?string $requestTokenSecret): InexogyConsumer
    {
        $this->requestTokenSecret = $requestTokenSecret;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): InexogyConsumer
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getAccessTokenSecret(): ?string
    {
        return $this->accessTokenSecret;
    }

    public function setAccessTokenSecret(?string $accessTokenSecret): InexogyConsumer
    {
        $this->accessTokenSecret = $accessTokenSecret;

        return $this;
    }
}
