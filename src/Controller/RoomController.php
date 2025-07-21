<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\User;
use App\Entity\RoomParticipant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RoomController extends AbstractController
{
    #[Route('/api/rooms', name: 'api_create_room', methods: ['POST'])]
    public function createRoom(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;
        $game = $data['game'] ?? null;
        $maxPlayer = $data['maxPlayer'] ?? null;
        // get authenticated user id to put it in the room owner
        $token = $request->headers->get('Authorization');
        $userId = null;
        if ($token && preg_match('/Bearer (.+)/', $token, $matches)) {
            $userId = $request->headers->get('X-User-Id');
        }
        if (!$userId) {
            $ownerIri = $data['owner'] ?? null;
            if (!$ownerIri) {
                return $this->json(['error' => 'Missing owner information'], 400);
            }
            if (preg_match('#/api/users/(\\d+)#', $ownerIri, $matches)) {
                $userId = $matches[1];
            } else {
                return $this->json(['error' => 'Invalid owner IRI'], 400);
            }
        }
        $owner = $em->getRepository(User::class)->find($userId);
        if (!$owner) {
            return $this->json(['error' => 'Owner not found'], 404);
        }

        $room = new Room();
        $room->setName($name);
        $room->setGame($game);
        $room->setMaxPlayer($maxPlayer);
        $room->setOwner($owner->getId());
        $em->persist($room);
        $em->flush();

        return $this->json([
            'id' => $room->getId(),
            'name' => $room->getName(),
            'game' => $room->getGame(),
            'maxPlayer' => $room->getMaxPlayer(),
            'owner' => $owner->getId(),
            'participant' => [
                'id' => $participant->getId(),
                'user' => $owner->getId(),
                'username' => $participant->getUsername(),
                'avatar' => $participant->getAvatar(),
                'room' => $room->getId(),
                'owner' => $participant->isOwner()
            ]
        ], 201);
    }

    #[Route('/api/rooms/{id}/participants', name: 'api_room_participants', methods: ['GET'])]
    public function getRoomParticipants($id, EntityManagerInterface $em): JsonResponse
    {
        $room = $em->getRepository(Room::class)->find($id);
        if (!$room) {
            return $this->json(['error' => 'Room not found'], 404);
        }
        $participants = $em->getRepository(RoomParticipant::class)->findBy(['room' => $room]);
        $data = array_map(function($participant) {
            return [
                'id' => $participant->getId(),
                'user' => $participant->getUser()->getId(),
                'username' => $participant->getUsername(),
                'avatar' => $participant->getAvatar(),
                'owner' => $participant->isOwner()
            ];
        }, $participants);
        return $this->json($data);
    }

}
