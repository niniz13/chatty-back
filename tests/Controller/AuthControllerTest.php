<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    private function ensureTestUserExists(): void
    {
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // delete previous "testuser"
        $existing = $em->getRepository(User::class)->findBy(['username' => 'testuser']);
        foreach ($existing as $user) {
            $em->remove($user);
        }
        $em->flush();

        // create an user
        $user = new User();
        $user->setUsername('testuser');
        $user->setAvatar('avatar.png');
        $user->setGamePlayed(0);
        $user->setGameWon(0);

        $hashedPassword = $passwordHasher->hashPassword($user, 'goodpassword');
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();
    }

    public function testLoginInvalidUsername(): void
    {
        $client = static::createClient();
        $this->ensureTestUserExists();

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'wronguser',
                'password' => 'anything',
            ]),
        );

        $this->assertResponseStatusCodeSame(401);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid credentials.', $data['error']);
    }

    public function testLoginInvalidPassword(): void
    {
        $client = static::createClient();
        $this->ensureTestUserExists();

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'testuser',
                'password' => 'wrongpassword',
            ]),
        );

        $this->assertResponseStatusCodeSame(401);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid credentials.', $data['error']);
    }

    public function testLoginSuccess(): void
    {
        $client = static::createClient();
        $this->ensureTestUserExists();

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'testuser',
                'password' => 'goodpassword',
            ]),
        );

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertEquals('testuser', $data['user']['username']);
    }
}
