<?php

namespace App\Controller\Admin;

use App\Controller\VideoController;
use App\Entity\Feedback;
use App\Entity\Reaction;
use App\Entity\Report;
use App\Entity\User;
use App\Entity\Video;
use App\Repository\FeedbackRepository;
use App\Repository\ReactionRepository;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private VideoRepository $videoRepository,
        private ReportRepository $reportRepository,
        private UserRepository $userRepository,
        private ReactionRepository $reactionRepository,
        private FeedbackRepository $feedbackRepository,
    )
    {
        $this->videoRepository = $videoRepository;
        $this->reportRepository = $reportRepository;
        $this->userRepository = $userRepository;
        $this->reactionRepository = $reactionRepository;
        $this->feedbackRepository = $feedbackRepository;
    }

    public function index(): Response
    {
        $videoCount = $this->videoRepository->count();
        $reportCount = $this->reportRepository->count();
        $reactionCount = $this->reactionRepository->count();
        $feedbackCount = $this->feedbackRepository->count();
        $userCount = $this->userRepository->count();

        $recentVideos = $this->videoRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $recentReports = $this->reportRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $recentFeedbacks = $this->feedbackRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $users = $this->userRepository->getUsersPerDay();
        $usersLabel = json_encode(array_column($users, 'date'));
        $usersData = json_encode(array_column($users, 'count'));

        return $this->render('admin/dashboard.html.twig', [
            'videoCount' => $videoCount,
            'reportCount' => $reportCount,
            'reactionCount' => $reactionCount,
            'feedbackCount' => $feedbackCount,
            'userCount' => $userCount,
            'recentVideos' => $recentVideos,
            'usersLabel' => $usersLabel,
            'usersData' => $usersData,
            'recentReports' => $recentReports,
            'recentFeedbacks' => $recentFeedbacks,
        ]);

//        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
//        return $this->redirect($adminUrlGenerator->setController(VideoCrudController::class)->generateUrl());
    }


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Административная панель');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Videos', 'fas fa-video', Video::class);
        yield MenuItem::linkToCrud('Reports', 'fas fa-flag', Report::class);
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Reactions', 'fas fa-user', Reaction::class);
        yield MenuItem::linkToCrud('Feedbacks', 'fas fa-user', Feedback::class);
    }
}
