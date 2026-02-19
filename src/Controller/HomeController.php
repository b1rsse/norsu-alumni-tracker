<?php

namespace App\Controller;

use App\Repository\AlumniRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function root(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/home', name: 'app_home')]
    public function index(AlumniRepository $alumniRepo, UserRepository $userRepo): Response
    {
        if (!$this->getUser()) {
            return $this->render('home/landing.html.twig');
        }

        // Dashboard stats
        $totalAlumni = $alumniRepo->count([]);
        $employed = $alumniRepo->count(['employmentStatus' => 'Employed']);
        $unemployed = $alumniRepo->count(['employmentStatus' => 'Unemployed']);
        $selfEmployed = $alumniRepo->count(['employmentStatus' => 'Self-Employed']);
        $totalUsers = $userRepo->count([]);

        $recentAlumni = $alumniRepo->findBy([], ['id' => 'DESC'], 5);

        // Employment rate
        $employmentRate = $totalAlumni > 0 ? round(($employed + $selfEmployed) / $totalAlumni * 100, 1) : 0;

        // Course distribution
        $courseStats = $alumniRepo->createQueryBuilder('a')
            ->select('a.course, COUNT(a.id) as total')
            ->groupBy('a.course')
            ->orderBy('total', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('home/index.html.twig', [
            'totalAlumni' => $totalAlumni,
            'employed' => $employed,
            'unemployed' => $unemployed,
            'selfEmployed' => $selfEmployed,
            'totalUsers' => $totalUsers,
            'employmentRate' => $employmentRate,
            'recentAlumni' => $recentAlumni,
            'courseStats' => $courseStats,
        ]);
    }
}
