<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
use App\Repository\VideoRepository;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Object_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Video')]
final class VideoController extends AbstractController
{
    #[Route('/', name: 'Главная', methods: ['GET'])]
    public function index(UserManager $userManager, VideoRepository $videoRepository): Response
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

    #[Route('/upload', name: 'app_video_upload')]
    public function uploadVideo(Request $request, EntityManagerInterface $entityManager): Response{
        try {
            $video = new Video();
            $form = $this->createForm(VideoType::class, $video);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $videoUploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/videos';
                $videoFile = $form->get('videoFile')->getData();
                if ($videoFile) {
                    $filename = uniqid() . '.' . $videoFile->guessExtension();
                    $videoFile->move($videoUploadDir, $filename);
                    $video->setFilePath('/uploads/videos/' . $filename);
                    $video->setCreatedAt(new \DateTimeImmutable());
                    $entityManager->persist($video);
                    $entityManager->flush();
                    $this->addFlash('success', 'Video uploaded successfully');
                    return $this->redirectToRoute('app_video_upload');
                }
            }
            return $this->render('video/upload.html.twig', [
                'form' => $form->createView(),]);
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage());
        }
    }

    #[Route('/api/get-videos', name: 'app_videos_api', methods: ['GET'])]
    #[OA\Get(
        description: 'Получение списка видео с пагинацией',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешное получение списка видео',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'videos',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 123),
                                    new OA\Property(property: 'title', type: 'string', example: 'Название видео'),
                                    new OA\Property(property: 'filePath', type: 'string', example: '/uploads/videos/file.mp4'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2023-01-01 12:00:00'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'nextPage', type: 'integer', nullable: true, example: 2)
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Некорректные параметры запроса'),
            new OA\Response(response: 500, description: 'Ошибка сервера')
        ]
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Номер страницы',
        in: 'query',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Количество элементов на странице',
        in: 'query',
        schema: new OA\Schema(type: 'integer', maximum: 100)
    )]
    public function getVideos(
        Request $request,
        VideoRepository $videoRepository,
    ): JsonResponse {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 2);

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

    #[Route('api/upload-video', name: 'app_video_upload-api', methods: ['POST'])]
    #[OA\Post(
        description: 'API загрузка нового видео',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'videoFile',
                            type: 'string',
                            format: 'binary',
                        ),
                        new OA\Property(
                            property: 'title',
                            type: 'string',
                            example: 'Смешной малыш',
                        ),
                        new OA\Property(
                            property: 'description',
                            type: 'text',
                            example: 'Малыш бежиьт по газону и падает на щенка',
                        )]
                ),
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Видео успешно загружено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 123),
                        new OA\Property(property:'title', type: 'string', example: 'Смешной малыш'),
                        new OA\Property(property: 'description', type: 'text', example: 'Малыш бежиьт по газону и падает на щенка')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка загрузки',
            )
        ]
    )]
    public function uploadVideoApi(Request $request, EntityManagerInterface $entityManager): JsonResponse{
        try {

            $videoFile = $request->files->get('videoFile');
            $video = new Video();
            $videoUploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/videos';

            if ($videoFile) {
                $filename = uniqid() . '.' . $videoFile->guessExtension();
                $videoFile->move($videoUploadDir, $filename);
                $video->setFilePath('/uploads/videos/' . $filename);
                $video->setTitle($request->request->get('title'));
                $video->setDescription($request->request->get('description'));

                $video->setCreatedAt(new \DateTimeImmutable());

                $entityManager->persist($video);
                $entityManager->flush();

                return $this->json([
                    'id' => $video->getId(),
                    'title' => $video->getTitle(),
                    'filePath' => $video->getFilePath(),
                    'createdAt' => $video->getCreatedAt()->format('Y-m-d H:i:s'),
                    'description' => $video->getDescription(),
                ]);
            }
            return $this->json([
                'error' => 'No video uploaded',
            ], 400);
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage());
        }
    }

    #[Route('/api/get-video/{id}', name: 'app_video_api', methods: ['GET'])]
    #[OA\Get(
        description: 'Получение информации о видео по ID',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешное получение видео',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 123),
                        new OA\Property(property: 'title', type: 'string', example: 'Название видео'),
                        new OA\Property(property: 'filePath', type: 'string', example: '/uploads/videos/file.mp4'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2023-01-01 12:00:00'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Видео не найдено')
        ]
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'ID видео',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
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
