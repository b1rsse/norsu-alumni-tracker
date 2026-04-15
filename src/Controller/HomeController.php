<?php

namespace App\Controller;

use App\Repository\AlumniRepository;
use App\Repository\AnnouncementRepository;
use App\Repository\AuditLogRepository;
use App\Repository\GtsSurveyRepository;
use App\Repository\JobPostingRepository;
use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function root(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/about', name: 'app_about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route('/contact-us', name: 'app_contact', methods: ['GET'])]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }

    #[Route('/faq', name: 'app_faq', methods: ['GET'])]
    public function faq(): Response
    {
        return $this->render('home/faq.html.twig');
    }

    #[Route('/home', name: 'app_home')]
    public function index(AlumniRepository $alumniRepo, UserRepository $userRepo, JobPostingRepository $jobRepo, AnnouncementRepository $announcementRepo, GtsSurveyRepository $gtsRepo, AuditLogRepository $auditLogRepo, EntityManagerInterface $em, CacheInterface $cache): Response
    {
        if (!$this->getUser()) {
            return $this->render('home/landing.html.twig');
        }

        // Common stats (cached for 5 minutes)
        $stats = $cache->get('dashboard_common_stats', function (ItemInterface $item) use ($alumniRepo, $userRepo) {
            $item->expiresAfter(300);
            $totalAlumni = $alumniRepo->count([]);
            $employed = $alumniRepo->count(['employmentStatus' => 'Employed']);
            $unemployed = $alumniRepo->count(['employmentStatus' => 'Unemployed']);
            $selfEmployed = $alumniRepo->count(['employmentStatus' => 'Self-Employed']);
            $totalUsers = $userRepo->count([]);
            $employmentRate = $totalAlumni > 0 ? round(($employed + $selfEmployed) / $totalAlumni * 100, 1) : 0;
            return compact('totalAlumni', 'employed', 'unemployed', 'selfEmployed', 'totalUsers', 'employmentRate');
        });
        extract($stats);

        $courseStats = $alumniRepo->createQueryBuilder('a')
            ->select('a.course, COUNT(a.id) as total')
            ->where('a.course IS NOT NULL')
            ->groupBy('a.course')
            ->orderBy('total', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $employmentStatusRows = $alumniRepo->countGroupedByEmploymentStatus();
        $employmentStatusChart = [
            'labels' => array_column($employmentStatusRows, 'employmentStatus'),
            'values' => array_column($employmentStatusRows, 'total'),
        ];

        // Admin dashboard (rendered inline) / Staff dashboard (redirect to /staff)
        if ($this->isGranted('ROLE_ADMIN')) {
            $pendingUsers = $userRepo->count(['accountStatus' => 'pending']);
            $pendingList = $userRepo->findBy(['accountStatus' => 'pending'], ['dateRegistered' => 'DESC'], 5);
            $activeJobs = $jobRepo->count(['isActive' => true]);
            $totalJobs = $jobRepo->count([]);
            $totalSurveyResponses = $gtsRepo->count([]);

            // Job-course alignment rate
            $jobRelated = $alumniRepo->count(['jobRelatedToCourse' => true]);
            $employedTotal = $employed + $selfEmployed;
            $alignmentRate = $employedTotal > 0 ? round($jobRelated / $employedTotal * 100, 1) : 0;

            $recentAlumni = $alumniRepo->findBy([], ['id' => 'DESC'], 5);
            $recentAnnouncements = $announcementRepo->findBy([], ['datePosted' => 'DESC'], 5);

            // Role counts using efficient aggregate queries
            $conn = $em->getConnection();
            $adminCount = (int) $conn->fetchOne("SELECT COUNT(*) FROM `user` WHERE JSON_CONTAINS(roles, '\"ROLE_ADMIN\"')");
            $staffCount = (int) $conn->fetchOne("SELECT COUNT(*) FROM `user` WHERE JSON_CONTAINS(roles, '\"ROLE_STAFF\"')");
            $studentCount = $totalUsers - $adminCount - $staffCount;

            // Online users: those with lastActivity within the last 5 minutes
            $threshold = (new \DateTime())->modify('-5 minutes');
            $activeUsers = (int) $em->createQuery(
                'SELECT COUNT(u.id) FROM App\Entity\User u WHERE u.lastActivity IS NOT NULL AND u.lastActivity > :threshold'
            )->setParameter('threshold', $threshold)->getSingleScalarResult();

            return $this->render('admin/dashboard.html.twig', [
                'totalAlumni' => $totalAlumni,
                'employed' => $employed,
                'unemployed' => $unemployed,
                'selfEmployed' => $selfEmployed,
                'totalUsers' => $totalUsers,
                'employmentRate' => $employmentRate,
                'recentAlumni' => $recentAlumni,
                'courseStats' => $courseStats,
                'pendingUsers' => $pendingUsers,
                'pendingList' => $pendingList,
                'activeJobs' => $activeJobs,
                'totalJobs' => $totalJobs,
                'totalSurveyResponses' => $totalSurveyResponses,
                'alignmentRate' => $alignmentRate,
                'recentAnnouncements' => $recentAnnouncements,
                'recentAuditLogs' => $auditLogRepo->findRecent(10),
                'adminCount' => $adminCount,
                'staffCount' => $staffCount,
                'studentCount' => $studentCount,
                'activeUsers' => $activeUsers,
                'employmentStatusChart' => $employmentStatusChart,
            ]);
        }

        // Staff dashboard (dedicated /staff area)
        if ($this->isGranted('ROLE_STAFF')) {
            return $this->redirectToRoute('staff_dashboard');
        }

        // Alumni dashboard (ROLE_ALUMNI)
        if ($this->isGranted('ROLE_ALUMNI')) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                return $this->redirectToRoute('app_login');
            }

            $alumni = $user->getAlumni();

            $recentAnnouncements = $announcementRepo->findBy(['isActive' => true], ['datePosted' => 'DESC'], 9);
            $recentJobs = $jobRepo->findBy(['isActive' => true], ['datePosted' => 'DESC'], 8);
            $milestoneAlumni = $alumniRepo->createQueryBuilder('a')
                ->where('a.deletedAt IS NULL')
                ->andWhere("(a.honorsReceived IS NOT NULL AND a.honorsReceived <> '') OR (a.careerAchievements IS NOT NULL AND a.careerAchievements <> '') OR (a.jobTitle IS NOT NULL AND a.jobTitle <> '')")
                ->orderBy('a.yearGraduated', 'DESC')
                ->addOrderBy('a.id', 'DESC')
                ->setMaxResults(4)
                ->getQuery()
                ->getResult();
            $hasGtsSurvey = $gtsRepo->count(['user' => $user]) > 0;

            return $this->render('home/alumni_dashboard.html.twig', [
                'alumni' => $alumni,
                'recentAnnouncements' => $recentAnnouncements,
                'recentJobs' => $recentJobs,
                'milestoneAlumni' => $milestoneAlumni,
                'hasGtsSurvey' => $hasGtsSurvey,
            ]);
        }

        // Student / default dashboard
        $activeJobs = $jobRepo->count(['isActive' => true]);

        return $this->render('home/student_dashboard.html.twig', [
            'activeJobs' => $activeJobs,
            'employmentRate' => $employmentRate,
            'courseStats' => $courseStats,
        ]);
    }
}
