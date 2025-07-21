<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Room;
use App\Entity\RoomParticipant;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RoomParticipantControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private $user;
    private $room;
    private $participant;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->em = self::getContainer()->get('doctrine')->getManager();

        // Create test user
        $this->user = new User();
        $this->user->setUsername('testuser');
        $this->user->setPassword('testpassword');
        $this->user->setAvatar('/avatar1.svg');
        $this->em->persist($this->user);

        // Create test room
        $this->room = new Room();
        $this->room->setName('Test Room');
        $this->room->setGame('Test Game');
        $this->room->setMaxPlayer(4);
        $this->room->setOwner(1); // Set a dummy owner ID
        $this->em->persist($this->room);

        $this->em->flush();
    }

    protected function tearDown(): void
    {
        if ($this->participant) {
            $this->em->remove($this->participant);
        }

        $this->em->remove($this->room);
        $this->em->remove($this->user);
        $this->em->flush();
    }

    public function testCreateRoomParticipantSuccess(): void
    {
        $this->client->request(
            'POST',
            '/api/room-participants',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'room' => $this->room->getId(),
                'user' => $this->user->getId(),
                'owner' => true
            ])
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($this->user->getId(), $data['user']);
        $this->assertSame($this->room->getId(), $data['room']);

        // Save for cleanup
        $this->participant = $this->em->getRepository(RoomParticipant::class)->find($data['id']);
    }

    public function testCreateRoomParticipantMissingFields(): void
    {
        $this->client->request(
            'POST',
            '/api/room-participants',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([]) // empty payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateRoomParticipantRoomNotFound(): void
    {
        $this->client->request(
            'POST',
            '/api/room-participants',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'room' => 9999,
                'user' => $this->user->getId()
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteRoomParticipantSuccess(): void
    {
        // First create a participant to delete
        $participant = new RoomParticipant();
        $participant->setRoom($this->room);
        $participant->setUser($this->user);
        $participant->setUsername($this->user->getUsername());
        $participant->setAvatar($this->user->getAvatar());
        $participant->setOwner(false);

        $this->em->persist($participant);
        $this->em->flush();

        $this->client->request('DELETE', '/api/room-participants/' . $participant->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteRoomParticipantNotFound(): void
    {
        $this->client->request('DELETE', '/api/room-participants/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
