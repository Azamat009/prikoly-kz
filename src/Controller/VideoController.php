<?php

namespace App\Controller;

use App\Entity\Video;
use App\Repository\VideoRepository;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VideoController extends AbstractController
{
    #[Route('/', name: 'Главная')]
    public function index(): Response
    {
        return $this->render('video/index.html.twig', [
            'controller_name' => 'VideoController',
        ]);
    }
    #[Route('/api/videos', name: 'app_videos_api')]
    public function getVideos(
        Request $request,
        UserManager $userManager,
        VideoRepository $videoRepository,
    ): JsonResponse {
        $user = $userManager->getCurrentUser();
        $response = new JsonResponse();

        if ($userManager->isNewUser()){
            $response->headers->setCookie(
                $userManager->createNewUserCookie($user)
            );
        }

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $videos = $videoRepository->findPaginatedVideos($page, $limit);

        return $this->json([
            'videos' => array_map(fn ($video) => [
                'id' => $video->getId(),
                'title' => $video->getTitle(),
                'description' => $video->getDescription(),
                'filePath' => $video->getFilePath(),
                'createdAt' => $video->getCreatedAt()->format('Y-m-d H:i:s'),
            ],$videos),
            'nextPage' => count($videos) === $limit ? $page + 1 : null,
        ]);
    }

    public function uploadVideo(Request $request, string $videoUploadDir): Response{
        return [];
    }
}
