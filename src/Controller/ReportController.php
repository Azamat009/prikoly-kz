<?php

namespace App\Controller;

use App\Entity\Report;
use App\Repository\ReportRepository;
use App\Repository\VideoRepository;
use App\Service\UserManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Report')]
final class ReportController extends AbstractController
{
    #[Route('/report', name: 'app_report')]
    public function index(): Response
    {
        return $this->render('report/index.html.twig', [
            'controller_name' => 'ReportController',
        ]);
    }

    #[Route('api/get-reports', name: 'app_reports_api', methods: ['GET'])]
    #[OA\Get(
        description: 'Получение списка жалоб с пагинацией',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешное получение списка жалоб',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'reports',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', readOnly: true, example: 3),
                                    new OA\Property(property: 'userId', type: 'integer', readOnly: true, example: 2),
                                    new OA\Property(property: 'reason', type: 'string', readOnly: true, example: 'Нарушение авторских прав'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2023-01-01 12:00:00'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'nextPage', type: 'integer', nullable: true, example: 4),
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
    #[OA\Parameter(
        name: 'limit',
        description: 'Количество элементов на странице',
        in: 'query',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Номер страницы',
        in: 'query',
        schema: new OA\Schema(type: 'integer')
    )]
    public function getReports(Request $request, ReportRepository $reportRepository): JsonResponse {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 10);

            $reports = $reportRepository->findPaginatedReports($page, $limit);

            return $this->json([
                'reports' => array_map(fn($report) => [
                    'id' => $report->getId(),
                    'userId' => $report->getUserId(),
                    'reason' => $report->getReason(),
                    'createdAt' => $report->getCreatedAt()->format('Y-m-d H:i:s'),
                    ], $reports),
                'nextPage' => count($reports) === $limit ? $page + 1 : null
                ]
            );

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    #[Route('api/get-report/{id}', name: 'app_report_api', methods: ['GET'])]
    #[OA\Get(
        description: 'Получить жалобу по ID',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Жалоба по ID успешно получена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', readOnly: true, example: 3),
                        new OA\Property(property: 'userId', type: 'integer', readOnly: true, example: 2),
                        new OA\Property(property: 'reason', type: 'string', readOnly: true, example: 'Нарушение авторских прав'),
                        new OA\Property(property: 'createdAt', readOnly: true, format: 'date-time', example: '2023-01-01 12:00:00'),]
                )
            )]
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'ID жалобы',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    public function getReport(int $id, Request $request, ReportRepository $reportRepository): JsonResponse {
        try {
            $report = $reportRepository->find($id);

            if (!$report) {
                throw $this->createNotFoundException('Report not found');
            }
            return $this->json([
                'id' => $report->getId(),
                'userId' => $report->getUserId(),
                'reason' => $report->getReason(),
                'createdAt' => $report->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    #[Route('api/create-report', name: 'app_create_report_api', methods: ['POST'])]
    #[OA\Post(
        description: 'Создание жалобы',
        requestBody: new OA\RequestBody(
            description: 'Данные для создания жалобы',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'videoId', type: 'integer', example: 3),
                    new OA\Property(property: 'reason', type: 'string', example: 'Жалоба на видео')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Жалоба отправлена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'videoId', type: 'integer', example: 2),
                        new OA\Property(property: 'reason', type: 'string', example: 'Жалоба на видео'),
                        new OA\Property(property: 'createdAt', format: 'date-time', example: '2023-01-01 12:00:00'),
                        new OA\Property(property: 'userId', example: 1)
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Неккоректные данные',
            ),
            new OA\Response(
                response: 500,
                description: 'Ошибка сервера',
            )
            ]
    )]
    public function createReport(
        Request $request,
        EntityManagerInterface
        $entityManager,
        ReportRepository $reportRepository,
        UserManager $userManager,
        VideoRepository $videoRepository,
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            if(isset($data['reason'], $data['userId'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'missing required data'
                ], 400);
            }

            $video = $videoRepository->find($data['videoId']);
            if (!$video) {
                throw $this->createNotFoundException('video not found');
            }
            $report = new Report();
            $report->addUserId($userManager->getCurrentUser());
            $report->setReason($data['reason']);
            $report->addVideoId($video);
            $report->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($report);
            $entityManager->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'report added successfully'
            ]);

        } catch (\Exception $e) {
            throw $this->createNotFoundException('Error: '.$e->getMessage());
        }
    }
}
