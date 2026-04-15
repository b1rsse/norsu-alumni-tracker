<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\GtsSurvey;
use App\Form\GtsSurveyType;
use App\Repository\GtsSurveyQuestionRepository;
use App\Repository\GtsSurveyRepository;
use App\Service\AuditLogger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gts')]
class GtsController extends AbstractController
{
    public function __construct(private AuditLogger $audit) {}

    /**
     * Fill out the CHED Graduate Tracer Survey.
     */
    #[Route('/new', name: 'gts_new', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        GtsSurveyRepository $surveyRepository,
        GtsSurveyQuestionRepository $questionRepository,
    ): Response
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_STAFF')) {
            $this->addFlash('warning', 'Surveys are for Alumni accounts only.');

            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_home');
            }

            return $this->redirectToRoute('staff_dashboard');
        }

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('Login required.');
        }

        // Access should be based on verified account status, not on whether an Alumni row is already linked.
        if ($currentUser->getAccountStatus() !== 'active') {
            $this->addFlash('danger', 'Only verified alumni accounts can submit the tracer survey.');
            return $this->redirectToRoute('app_profile');
        }

        if ($surveyRepository->hasUserSubmitted($currentUser)) {
            $this->addFlash('warning', 'You have already completed this survey.');
            return $this->redirectToRoute('app_dashboard');
        }

        $survey = new GtsSurvey();
        $survey->setUser($currentUser);

        // Pre-fill from logged-in user
        $user = $currentUser;
        $survey->setName($user->getLastName() . ', ' . $user->getFirstName());
        $survey->setEmailAddress($user->getEmail());
        $survey->setInstitutionCode($this->resolveInstitutionCode());
        $survey->setControlCode($this->generateControlCode($currentUser));

        $dynamicQuestions = $questionRepository->createQueryBuilder('q')
            ->where('q.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('q.section', 'ASC')
            ->addOrderBy('q.sortOrder', 'ASC')
            ->addOrderBy('q.id', 'ASC')
            ->getQuery()
            ->getResult();

        $form = $this->createForm(GtsSurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleSubmission($request, $survey, $currentUser, $em, $dynamicQuestions);

            $this->audit->log('GTS Survey submitted', 'GtsSurvey', $survey->getId());

            $this->addFlash('success', 'Success: Your Graduate Tracer Survey was saved and your profile tracer status is now TRACED.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('gts/new.html.twig', [
            'form' => $form,
            'institutionCode' => $survey->getInstitutionCode(),
            'controlCode' => $survey->getControlCode(),
            'gtsQuestions' => $dynamicQuestions,
            'dynamicAnswers' => $request->request->all('dynamic_answers'),
            'hasAlreadyResponded' => false,
        ]);
    }

    /**
     * Thank-you page after successful submission.
     */
    #[Route('/thank-you', name: 'gts_thankyou', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function thankYou(): Response
    {
        return $this->render('gts/thank_you.html.twig');
    }

    /**
     * Admin/Staff: view all submitted surveys.
     */
    #[Route('/', name: 'gts_index', methods: ['GET'])]
    #[IsGranted('ROLE_STAFF')]
    public function index(Request $request, GtsSurveyRepository $repo): Response
    {
        $page  = max(1, $request->query->getInt('page', 1));
        $limit = 20;

        $qb = $repo->createQueryBuilder('s')->orderBy('s.createdAt', 'DESC');
        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);
        $paginator = new Paginator($qb);
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        return $this->render('gts/index.html.twig', [
            'surveys' => $paginator,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
        ]);
    }

    /**
     * View a single survey response.
     */
    #[Route('/{id}', name: 'gts_show', methods: ['GET'])]
    #[IsGranted('ROLE_STAFF')]
    public function show(GtsSurvey $survey): Response
    {
        return $this->render('gts/show.html.twig', [
            'survey' => $survey,
        ]);
    }

    /**
     * Submission Bridge: map request data into survey JSON payloads,
     * then set linked alumni tracer status and submission timestamp.
     */
    private function handleSubmission(Request $request, GtsSurvey $survey, User $user, EntityManagerInterface $em, array $dynamicQuestions): void
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true) || in_array('ROLE_STAFF', $user->getRoles(), true)) {
            throw $this->createAccessDeniedException('Surveys are for Alumni accounts only.');
        }

        $survey->setInstitutionCode($this->resolveInstitutionCode());
        $survey->setControlCode($survey->getControlCode() ?: $this->generateControlCode($user));

        $allowedExamKeys = ['name', 'dateTaken', 'rating'];
        $allowedTrainingKeys = ['title', 'duration', 'institution'];

        $degreeRows = $request->request->all('gts_survey')['degrees'] ?? [];
        if (!empty($degreeRows)) {
            $parsed = [];
            foreach ($degreeRows as $row) {
                if (is_array($row) && !empty(array_filter($row))) {
                    $parsed[] = [
                        'college' => $row['college'] ?? null,
                        'yearGraduated' => $row['yearGraduated'] ?? null,
                    ];
                }
            }
            $survey->setEducationalAttainment($parsed ?: null);
        }

        $examRows = $request->request->all('exam_rows');
        if (!empty($examRows)) {
            $parsed = [];
            foreach ($examRows as $row) {
                if (is_array($row) && !empty(array_filter($row))) {
                    $parsed[] = array_intersect_key($row, array_flip($allowedExamKeys));
                }
            }
            $survey->setProfessionalExams($parsed ?: null);
        }

        $trainingRows = $request->request->all('training_rows');
        if (!empty($trainingRows)) {
            $parsed = [];
            foreach ($trainingRows as $row) {
                if (is_array($row) && !empty(array_filter($row))) {
                    $parsed[] = array_intersect_key($row, array_flip($allowedTrainingKeys));
                }
            }
            $survey->setTrainings($parsed ?: null);
        }

        $survey->setDynamicAnswers($this->sanitizeDynamicAnswers($request->request->all('dynamic_answers'), $dynamicQuestions));

        $alumni = $user->getAlumni();
        if ($alumni !== null) {
            $alumni->setTracerStatus('TRACED');
            $alumni->setLastTracerSubmissionAt(new \DateTime());
        }

        $em->persist($survey);
        $em->flush();
    }

    private function resolveInstitutionCode(): string
    {
        $value = (string) ($_ENV['GTS_INSTITUTION_CODE'] ?? $_SERVER['GTS_INSTITUTION_CODE'] ?? 'NORSU-GTS');

        return trim($value) !== '' ? trim($value) : 'NORSU-GTS';
    }

    private function generateControlCode(User $user): string
    {
        $prefix = (string) ($_ENV['GTS_CONTROL_CODE_PREFIX'] ?? $_SERVER['GTS_CONTROL_CODE_PREFIX'] ?? 'GTS');
        $prefix = trim($prefix) !== '' ? trim($prefix) : 'GTS';

        return sprintf('%s-%s-%d-%s', $prefix, date('Ymd'), (int) $user->getId(), strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)));
    }

    private function sanitizeDynamicAnswers(array $submittedAnswers, array $dynamicQuestions): array
    {
        $allowedIds = array_map(static fn ($question) => (string) $question->getId(), $dynamicQuestions);
        $clean = [];

        foreach ($submittedAnswers as $questionId => $value) {
            if (!in_array((string) $questionId, $allowedIds, true)) {
                continue;
            }

            if (is_array($value)) {
                $clean[(string) $questionId] = array_values(array_map('strval', $value));
                continue;
            }

            $clean[(string) $questionId] = trim((string) $value);
        }

        return $clean;
    }

}
