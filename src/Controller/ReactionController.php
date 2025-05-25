<?php

namespace App\Controller;

use App\Entity\Reaction;
use App\Entity\Video;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReactionController extends AbstractController
{
    #[Route('/reaction', name: 'app_reaction_create', methods: ['POST'])]
    public function createReaction(
        Request $request,
        UserManager $userManager,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = $userManager->getCurrentUser();

        $video = $entityManager->getRepository(Video::class)->find($data['videoId']);

        if (!$video) {
            return $this->json([
                'error' => 'Video not found',
            ], 404);
        }

        $reaction = (new Reaction())
            ->addUserId($user)
            ->addVideoId($video->getId())
            ->setEmotion($data['emotion'])
            ->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($reaction);
        $entityManager->flush();

        return $this->json([
            'status' => 'success'
        ]);
    }
}
