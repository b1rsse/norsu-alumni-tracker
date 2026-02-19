<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/feedback')]
class FeedbackController extends AbstractController
{
    #[Route('/', name: 'feedback_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $feedbacks = $em->getRepository(Feedback::class)->findBy([], ['dateSubmitted' => 'DESC']);

        return $this->render('feedback/index.html.twig', [
            'feedbacks' => $feedbacks,
        ]);
    }

    #[Route('/submit', name: 'feedback_submit', methods: ['GET', 'POST'])]
    public function submit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $alumni = $user?->getAlumni();

        $feedback = new Feedback();
        if ($alumni) {
            $feedback->setAlumni($alumni);
        }

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($feedback);
            $em->flush();
            $this->addFlash('success', 'Thank you for your feedback!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('feedback/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'feedback_show', methods: ['GET'])]
    public function show(Feedback $feedback): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('feedback/show.html.twig', [
            'feedback' => $feedback,
        ]);
    }

    #[Route('/{id}/delete', name: 'feedback_delete', methods: ['POST'])]
    public function delete(Feedback $feedback, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $feedback->getId(), $request->request->get('_token'))) {
            $em->remove($feedback);
            $em->flush();
            $this->addFlash('success', 'Feedback deleted.');
        }

        return $this->redirectToRoute('feedback_index');
    }
}
