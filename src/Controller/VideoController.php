<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
use App\Repository\VideoRepository;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VideoController extends AbstractController
{
    #[Route('/', name: 'Главная')]
    public function index(UserManager $userManager): Response
    {
        $user = $userManager->getCurrentUser();

        $response = $this->render('video/index.html.twig', [
            'controller_name' => 'VideoController',
        ]);

        if ($userManager->isNewUser()) {
            $response->headers->setCookie(
                $userManager->createNewUserCookie($user)
            );
        }

        return $response;
    }
    #[Route('/api/videos', name: 'app_videos_api')]
    public function getVideos(
        Request $request,
        VideoRepository $videoRepository,
    ): JsonResponse {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 5);

            $videos = $videoRepository->findPaginatedVideos($page, $limit);

            return $this->json([
                'videos' => array_map(fn($video) => [
                    'id' => $video->getId(),
                    'title' => $video->getTitle(),
                    'filePath' => $video->getFilePath(),
                    'createdAt' => $video->getCreatedAt()->format('Y-m-d H:i:s'),
                ], $videos),
                'nextPage' => count($videos) === $limit ? $page + 1 : null,
            ]);
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage());
        }
    }

    #[Route('/upload', name: 'video_upload')]
    public function uploadVideo(Request $request, EntityManagerInterface $entityManager): Response{
        try {
            $video = new Video();
            $form = $this->createForm(VideoType::class, $video);
            $form->handleRequest($request);
            $videoUploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/videos';

            if ($form->isSubmitted() && $form->isValid()) {
                $videoFile = $form->get('videoFile')->getData();

                if ($videoFile) {
                    $filename = uniqid() . '.' . $videoFile->guessExtension();
                    $videoFile->move($videoUploadDir, $filename);
                    $video->setFilePath('/uploads/videos/' . $filename);

                    $video->setCreatedAt(new \DateTimeImmutable());

                    $entityManager->persist($video);
                    $entityManager->flush();

                    $this->addFlash('success', 'Video uploaded successfully');

                    return $this->redirectToRoute('video_upload');
                }

            }
            return $this->render('video/upload.html.twig', [
                'form' => $form->createView(),]);
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage());
        }
    }

    #[Route('/api/video/{id}', name: 'app_video_api')]
    public function getVideo(int $id, VideoRepository $videoRepository): JsonResponse{
        try {
            $video = $videoRepository->find($id);

            if (!$video) {
                throw $this->createNotFoundException('Video not found');
            }

            return $this->json([
                'id' => $video->getId(),
                'title' => $video->getTitle(),
                'filePath' => $video->getFilePath(),
                'createdAt' => $video->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e){
            throw $this->createNotFoundException($e->getMessage());
        }
    }
}
