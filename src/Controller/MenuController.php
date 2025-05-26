<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Report;
use App\Form\FeedbackType;
use App\Form\ReportType;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MenuController extends AbstractController
{
    #[Route('/feedback', name: 'app_feedback')]
    public function feedback(
        Request $request,
        UserManager $userManager,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $feedback = new Feedback();
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            $feedback->setUser($userManager->getCurrentUser());
            $entityManager->persist($feedback);
            $entityManager->flush();

            return $this->redirectToRoute('/');
        }

        return $this->render('menu/feedback.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/report', name: 'app_report')]
    public function report(
        Request $request,
        UserManager $userManager,
        EntityManagerInterface $entityManager,
    ): Response{
        $report = new Report();
        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
//            $report->addUserId($userManager->getCurrentUser());
            $entityManager->persist($report);
            $entityManager->flush();

            return $this->redirectToRoute('/');
        }

        return $this->render('menu/report.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
