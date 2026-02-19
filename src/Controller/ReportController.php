<?php

namespace App\Controller;

use App\Repository\AlumniRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reports')]
class ReportController extends AbstractController
{
    #[Route('/', name: 'report_index', methods: ['GET'])]
    public function index(AlumniRepository $repo): Response
    {
        $totalAlumni = $repo->count([]);
        $employed = $repo->count(['employmentStatus' => 'Employed']);
        $selfEmployed = $repo->count(['employmentStatus' => 'Self-Employed']);
        $unemployed = $repo->count(['employmentStatus' => 'Unemployed']);

        // Employment Rate by Course
        $courseEmployment = $repo->createQueryBuilder('a')
            ->select('a.course, COUNT(a.id) AS total,
                      SUM(CASE WHEN a.employmentStatus = :emp OR a.employmentStatus = :self THEN 1 ELSE 0 END) AS employedCount')
            ->setParameter('emp', 'Employed')
            ->setParameter('self', 'Self-Employed')
            ->groupBy('a.course')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();

        // Salary Distribution
        $salaryDistribution = $repo->createQueryBuilder('a')
            ->select('a.monthlySalary, COUNT(a.id) AS total')
            ->where('a.monthlySalary IS NOT NULL')
            ->groupBy('a.monthlySalary')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();

        // Local vs Abroad
        $workAbroad = $repo->count(['workAbroad' => true]);
        $workLocal = $totalAlumni - $workAbroad;

        // Year distribution
        $yearDistribution = $repo->createQueryBuilder('a')
            ->select('a.yearGraduated, COUNT(a.id) AS total')
            ->where('a.yearGraduated IS NOT NULL')
            ->groupBy('a.yearGraduated')
            ->orderBy('a.yearGraduated', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Job Related to Course
        $jobRelated = $repo->count(['jobRelatedToCourse' => true]);
        $jobNotRelated = $repo->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.jobRelatedToCourse = false')
            ->getQuery()
            ->getSingleScalarResult();

        // Province Distribution
        $provinceDistribution = $repo->createQueryBuilder('a')
            ->select('a.province, COUNT(a.id) AS total')
            ->where('a.province IS NOT NULL AND a.province != :empty')
            ->setParameter('empty', '')
            ->groupBy('a.province')
            ->orderBy('total', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('report/index.html.twig', [
            'totalAlumni' => $totalAlumni,
            'employed' => $employed,
            'selfEmployed' => $selfEmployed,
            'unemployed' => $unemployed,
            'courseEmployment' => $courseEmployment,
            'salaryDistribution' => $salaryDistribution,
            'workAbroad' => $workAbroad,
            'workLocal' => $workLocal,
            'yearDistribution' => $yearDistribution,
            'jobRelated' => $jobRelated,
            'jobNotRelated' => $jobNotRelated,
            'provinceDistribution' => $provinceDistribution,
        ]);
    }
}
