<?php

namespace App\Controller;

use App\Entity\Reaction;
use App\Entity\Video;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Reaction')]
final class ReactionController extends AbstractController
{
    #[Route('api/create-reaction', name: 'app_reaction_create', methods: ['POST'])]
    #[OA\Post(
        description: 'Create a reaction video.',
        requestBody: new OA\RequestBody(
            description: 'Create a reaction video.',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'videoId', type: 'integer', example: 1),
                    new OA\Property(property: 'emotion', type: 'string', example: 'sad')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Create a reaction video.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'videoId', type: 'integer', example: 1),
                        new OA\Property(property: 'emotion', type: 'string', example: 'sad'),
                        new OA\Property(property: 'createdAt', format: 'date-time', example: new \DateTime('now')),
                        new OA\Property(property: 'userId', type: 'integer', example: 4),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Неккоректные параметры запроса',
            ),
            new OA\Response(
                response: 500,
                description: 'Ошибка сервера',
            )
        ]
    )]
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

    #[Route('/get-reactions', name: 'app_reaction_read', methods: ['GET'])]
    public function getReactions(Request $request): JsonResponse{
        return $this->json();
    }

    #[Route('/reactions-stats', name: 'app_reactions_stats', methods: ['GET'])]
    public function getStatsReactions(Request $request): JsonResponse{
        return $this->json();
    }


}
