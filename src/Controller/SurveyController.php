<?php

namespace App\Controller;

use App\Entity\GtsSurvey;
use App\Entity\User;
use App\Form\GtsSurveyType;
use App\Repository\GtsSurveyRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SurveyController extends AbstractController
{
    #[Route('/admin/surveys/analytics', name: 'admin_survey_analytics', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminIndex(GtsSurveyRepository $surveyRepository): Response
    {
        $surveys = $surveyRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/survey_analytics.html.twig', [
            'surveys' => $surveys,
            'totalResponses' => count($surveys),
        ]);
    }

    #[Route('/admin/survey/preview/{id}', name: 'admin_survey_preview', methods: ['GET'], requirements: ['id' => '\\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function preview(int $id, GtsSurveyRepository $surveyRepository): Response
    {
        $survey = $id === 0 ? $this->createMockSurvey() : $surveyRepository->find($id);

        if (!$survey instanceof GtsSurvey) {
            throw $this->createNotFoundException('Survey preview data not found.');
        }

        $form = $this->createForm(GtsSurveyType::class, $survey, [
            'disabled' => true,
        ]);

        return $this->render('admin/survey_preview.html.twig', [
            'form' => $form,
            'isPreviewMode' => true,
            'hasAlreadyResponded' => false,
        ]);
    }

    #[Route('/survey/download-certificate', name: 'survey_download_certificate', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function downloadCertificate(GtsSurveyRepository $surveyRepository): Response
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

        if (!$surveyRepository->hasUserSubmitted($currentUser)) {
            $this->addFlash('warning', 'Please complete the survey first before downloading your certificate.');
            return $this->redirectToRoute('gts_new');
        }

        $survey = $surveyRepository->findOneByUser($currentUser);
        if ($survey === null) {
            $this->addFlash('danger', 'Survey record not found.');
            return $this->redirectToRoute('app_dashboard');
        }

        $alumni = $currentUser->getAlumni();
        $fullName = trim((string) $currentUser->getFullName());
        if ($alumni !== null) {
            $fullName = trim($alumni->getFirstName() . ' ' . $alumni->getLastName());
        }

        $graduationDate = null;
        if ($alumni?->getDateGraduated() !== null) {
            $graduationDate = $alumni->getDateGraduated();
        } elseif ($alumni?->getYearGraduated() !== null) {
            $graduationDate = \DateTimeImmutable::createFromFormat('Y-m-d', $alumni->getYearGraduated() . '-01-01') ?: null;
        }

        $html = $this->renderView('survey/certificate.html.twig', [
            'fullName' => $fullName,
            'graduationDate' => $graduationDate,
            'surveyDate' => $survey->getCreatedAt(),
            'issuedAt' => new \DateTimeImmutable(),
        ]);

        $projectDir = (string) $this->getParameter('kernel.project_dir');
        $dompdfCacheDir = $projectDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'dompdf';
        (new Filesystem())->mkdir($dompdfCacheDir);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('tempDir', $dompdfCacheDir);
        $options->set('fontCache', $dompdfCacheDir);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $safeName = preg_replace('/[^A-Za-z0-9\-_]/', '-', $fullName) ?: 'alumni';
        $filename = sprintf('norsu-certificate-of-completion-%s.pdf', strtolower($safeName));

        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    private function createMockSurvey(): GtsSurvey
    {
        $survey = new GtsSurvey();
        $survey->setName('Sample Alumni, Preview User');
        $survey->setEmailAddress('preview@norsu.edu.ph');
        $survey->setPermanentAddress('NORSU Main Campus, Dumaguete City, Negros Oriental');
        $survey->setCivilStatus('Single');
        $survey->setSex('Female');
        $survey->setBirthday(new \DateTimeImmutable('2000-01-15'));
        $survey->setPresentlyEmployed('Yes');
        $survey->setPresentEmploymentStatus('Regular/Permanent');
        $survey->setSuggestions('This is a mock preview survey response for admin review.');

        return $survey;
    }
}
