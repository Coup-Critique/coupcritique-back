<?php

namespace App\Service;

use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class RefreshTokenManager
{
    protected string $token;
    protected string $refreshToken;
    protected RefreshTokenInterface $refreshTokenEntity;

    public function __construct(
        protected readonly JWTTokenManagerInterface $manager,
        protected readonly RefreshTokenManagerInterface $refreshTokenManager,
        protected readonly RefreshTokenGeneratorInterface $refreshGenerator
    ) {
    }

    public function create($user): array
    {
        $this->token = $this->manager->create($user);
        $this->refreshTokenEntity = $this->refreshGenerator->createForUserWithTtl($user, 2592000);

        $this->refreshTokenManager->save($this->refreshTokenEntity);

        $this->refreshToken = $this->refreshTokenEntity->getRefreshToken();

        return [$this->token, $this->refreshToken];
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getRefreshTokenEntity(): RefreshTokenInterface
    {
        return $this->refreshTokenEntity;
    }
}
