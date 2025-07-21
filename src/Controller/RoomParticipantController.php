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

class RoomParticipantController extends AbstractController
{
    #[Route('/api/room-participants', name: 'api_create_room_participant', methods: ['POST'])]
    public function createRoomParticipant(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $roomId = $data['room'] ?? null;
        $userId = $data['user'] ?? null;
        $isOwner = $data['owner'] ?? false;

        if (!$roomId || !$userId) {
            return $this->json(['error' => 'Missing room or user id'], 400);
        }

        $room = $em->getRepository(Room::class)->find($roomId);
        $user = $em->getRepository(User::class)->find($userId);
        if (!$room || !$user) {
            return $this->json(['error' => 'Room or User not found'], 404);
        }

        $participant = new RoomParticipant();
        $participant->setRoom($room);
        $participant->setUser($user);
        $participant->setUsername($user->getUsername());
        $participant->setAvatar($user->getAvatar());
        $participant->setOwner($isOwner);
        $em->persist($participant);
        $em->flush();

        return $this->json([
            'id' => $participant->getId(),
            'user' => $user->getId(),
            'username' => $participant->getUsername(),
            'avatar' => $participant->getAvatar(),
            'room' => $room->getId(),
            'owner' => $participant->isOwner()
        ], 201);
    }

    #[Route('/api/room-participants/{id}', name: 'api_delete_room_participant', methods: ['DELETE'])]
    public function deleteRoomParticipant(int $id, EntityManagerInterface $em): JsonResponse
    {
        $participant = $em->getRepository(RoomParticipant::class)->find($id);
        if (!$participant) {
            return $this->json(['error' => 'RoomParticipant not found'], 404);
        }
        $em->remove($participant);
        $em->flush();
        return $this->json(['message' => 'RoomParticipant deleted'], 204);
    }
}
