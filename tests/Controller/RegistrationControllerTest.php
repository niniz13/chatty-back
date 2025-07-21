<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        // Crée le client une fois pour tous les tests
        $this->client = static::createClient();
    }

    public function testRegisterMissingFields(): void
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['username' => '', 'password' => ''])
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(400);

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Username and password required', $responseData['error']);
    }

    public function testRegisterUsernameAlreadyTaken(): void
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['username' => 'existing_user', 'password' => 'any_password'])
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(409);

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Username already taken', $responseData['error']);
    }

    public function testRegisterSuccess(): void
    {
        $uniqueUsername = 'new_user_' . uniqid();

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['username' => $uniqueUsername, 'password' => 'securePassword123'])
        );

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User registered successfully', $responseData['message']);
    }
}
