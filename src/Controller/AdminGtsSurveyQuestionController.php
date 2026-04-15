<?php

namespace App\Controller;

use App\Entity\GtsSurveyQuestion;
use App\Form\Admin\GtsSurveyQuestionType;
use App\Repository\GtsSurveyQuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminGtsSurveyQuestionController extends AbstractController
{
    #[Route('/admin/gts/questions', name: 'admin_gts_questions_index', methods: ['GET'])]
    #[Route('/staff/gts/questions', name: 'staff_gts_questions_index', methods: ['GET'])]
    public function index(GtsSurveyQuestionRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STAFF');

        $questions = $repository->createQueryBuilder('q')
            ->orderBy('q.section', 'ASC')
            ->addOrderBy('q.sortOrder', 'ASC')
            ->addOrderBy('q.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/gts_questions/index.html.twig', [
            'questions' => $questions,
        ]);
    }

    #[Route('/admin/gts/questions/new', name: 'admin_gts_questions_new', methods: ['GET', 'POST'])]
    #[Route('/staff/gts/questions/new', name: 'staff_gts_questions_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STAFF');

        $question = new GtsSurveyQuestion();
        $form = $this->createForm(GtsSurveyQuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setOptions($this->parseOptions((string) $form->get('optionsCsv')->getData()));
            $entityManager->persist($question);
            $entityManager->flush();

            $this->addFlash('success', 'Survey question created.');

            return $this->redirectToRoute($this->getQuestionIndexRoute());
        }

        return $this->render('admin/gts_questions/form.html.twig', [
            'form' => $form,
            'question' => $question,
            'isEdit' => false,
        ]);
    }

    #[Route('/admin/gts/questions/{id}/edit', name: 'admin_gts_questions_edit', methods: ['GET', 'POST'])]
    #[Route('/staff/gts/questions/{id}/edit', name: 'staff_gts_questions_edit', methods: ['GET', 'POST'])]
    public function edit(GtsSurveyQuestion $question, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STAFF');

        $form = $this->createForm(GtsSurveyQuestionType::class, $question);
        $form->get('optionsCsv')->setData($question->getOptions() ? implode("\n", $question->getOptions()) : '');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setOptions($this->parseOptions((string) $form->get('optionsCsv')->getData()));
            $entityManager->flush();

            $this->addFlash('success', 'Survey question updated.');

            return $this->redirectToRoute($this->getQuestionIndexRoute());
        }

        return $this->render('admin/gts_questions/form.html.twig', [
            'form' => $form,
            'question' => $question,
            'isEdit' => true,
        ]);
    }

    #[Route('/admin/gts/questions/{id}/delete', name: 'admin_gts_questions_delete', methods: ['POST'])]
    #[Route('/staff/gts/questions/{id}/delete', name: 'staff_gts_questions_delete', methods: ['POST'])]
    public function delete(GtsSurveyQuestion $question, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STAFF');

        if ($this->isCsrfTokenValid('delete_gts_question_' . $question->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($question);
            $entityManager->flush();
            $this->addFlash('success', 'Survey question removed.');
        }

        return $this->redirectToRoute($this->getQuestionIndexRoute());
    }

    private function getQuestionIndexRoute(): string
    {
        return $this->isGranted('ROLE_ADMIN') ? 'admin_gts_questions_index' : 'staff_gts_questions_index';
    }

    private function parseOptions(string $rawOptions): ?array
    {
        $lines = preg_split('/\r\n|\r|\n/', $rawOptions) ?: [];
        $options = array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => $line !== ''));

        return $options === [] ? null : $options;
    }
}
