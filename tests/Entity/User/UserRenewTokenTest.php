<?php

declare(strict_types=1);

namespace App\Tests\User;

use App\Entity\PasswordTokenRenew;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserRenewTokenTest extends TestCase
{
    /**
     * @group Unit
     */
    public function testCreateRenewPasswordToken(): void
    {
        $user = new User();
        $token = new PasswordTokenRenew();

        $token->createToken($user);

        $this->assertIsObject($token->getUser());
        $this->assertNotNull($token->getTokenDateCreation());
    }

    /**
     * @group Unit
     */
    public function testPasswordTokenValidity()
    {
        $tokens = [
            new PasswordTokenRenew(),
            new PasswordTokenRenew(),
            new PasswordTokenRenew(),
        ];

        $user = new User();

        $tokens[0]->createToken($user);
        // 1 day ago != yesterday (time set to 0:00:00)
        $tokens[1]->setTokenDateCreation(new \DateTime('1 day ago'));
        $tokens[2]->setTokenDateCreation(new \DateTime('2 days ago'));

        $this->assertTrue($tokens[0]->isTokenValid());
        $this->assertNotTrue($tokens[1]->isTokenValid());
        $this->assertNotTrue($tokens[2]->isTokenValid());
    }
}
