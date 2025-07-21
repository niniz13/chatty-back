<?php

namespace App\Controller;

use App\Entity\GlobalMessage;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GlobalMessageController extends AbstractController
{
    #[Route('/api/global_messages', name: 'api_create_global_message', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $sender = $data['sender'] ?? null;
        $senderId = $data['senderId'] ?? null;
        $text = $data['text'] ?? null;
        $createdAt = $data['createdAt'] ?? null;

        if (!$sender || !$senderId || !$text) {
            return $this->json(['error' => 'Missing sender, senderId, or text'], 400);
        }

        $message = new GlobalMessage();
        $message->setSender($sender);
        $message->setSenderId((int)$senderId);
        $message->setText($text);
        $message->setCreatedAt($createdAt ? new \DateTime($createdAt) : new \DateTime());

        $em->persist($message);
        $em->flush();

        return $this->json([
            'id' => $message->getId(),
            'sender' => $message->getSender(),
            'senderId' => $message->getSenderId(),
            'text' => $message->getText(),
            'createdAt' => $message->getCreatedAt()?->format(DATE_ATOM),
        ], 201);
    }
}
