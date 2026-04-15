<?php

namespace App\Controller;

use App\Entity\JobPosting;
use App\Form\JobPostingType;
use App\Repository\JobPostingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/jobs')]
class JobBoardController extends AbstractController
{
    /**
     * Browse active job postings (all logged-in users).
     */
    #[Route('/', name: 'job_board_index', methods: ['GET'])]
    public function index(Request $request, JobPostingRepository $repo): Response
    {
        $search = $request->query->get('q', '');
        $type   = $request->query->get('type', '');

        if ($search || $type) {
            $qb = $repo->createQueryBuilder('j')
                ->where('j.isActive = true')
                ->andWhere('j.deadline IS NULL OR j.deadline >= :today')
                ->setParameter('today', new \DateTime('today'))
                ->orderBy('j.datePosted', 'DESC');

            if ($search) {
                $qb->andWhere('j.title LIKE :q OR j.companyName LIKE :q OR j.industry LIKE :q OR j.relatedCourse LIKE :q')
                   ->setParameter('q', '%' . $search . '%');
            }
            if ($type) {
                $qb->andWhere('j.employmentType = :type')->setParameter('type', $type);
            }

            $jobs = $qb->getQuery()->getResult();
        } else {
            $jobs = $repo->findActiveJobs();
        }

        $courses = $repo->createQueryBuilder('j')
            ->select('DISTINCT j.relatedCourse')
            ->where('j.relatedCourse IS NOT NULL')
            ->andWhere('j.isActive = true')
            ->orderBy('j.relatedCourse', 'ASC')
            ->getQuery()->getSingleColumnResult();

        return $this->render('job_board/index.html.twig', [
            'jobs'   => $jobs,
            'courses' => $courses,
            'search' => $search,
            'filter_type' => $type,
        ]);
    }

    /**
     * View job posting details.
     */
    #[Route('/{id}', name: 'job_board_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(JobPosting $job): Response
    {
        return $this->render('job_board/show.html.twig', [
            'job' => $job,
        ]);
    }

    /**
     * Create new job posting (Staff+ only).
     */
    #[Route('/create', name: 'job_board_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $job = new JobPosting();
        $form = $this->createForm(JobPostingType::class, $job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $job->setPostedBy($user);
            $em->persist($job);
            $em->flush();

            $this->addFlash('success', 'Job posting "' . $job->getTitle() . '" created successfully.');
            return $this->redirectToRoute('job_board_show', ['id' => $job->getId()]);
        }

        return $this->render('job_board/form.html.twig', [
            'form'  => $form->createView(),
            'title' => 'Post a New Job Opportunity',
            'job'   => $job,
        ]);
    }

    /**
     * Edit job posting (Staff+ only).
     */
    #[Route('/{id}/edit', name: 'job_board_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function edit(JobPosting $job, Request $request, EntityManagerInterface $em): Response
    {
        // Only the poster or an admin can edit
        if (!$this->isGranted('ROLE_ADMIN') && $job->getPostedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit your own job postings.');
        }

        $form = $this->createForm(JobPostingType::class, $job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $job->setDateUpdated(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Job posting updated successfully.');
            return $this->redirectToRoute('job_board_show', ['id' => $job->getId()]);
        }

        return $this->render('job_board/form.html.twig', [
            'form'  => $form->createView(),
            'title' => 'Edit Job — ' . $job->getTitle(),
            'job'   => $job,
        ]);
    }

    /**
     * Delete job posting (Admin only).
     */
    #[Route('/{id}/delete', name: 'job_board_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(JobPosting $job, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_job' . $job->getId(), $request->request->get('_token'))) {
            $em->remove($job);
            $em->flush();
            $this->addFlash('success', 'Job posting deleted.');
        }

        return $this->redirectToRoute('job_board_index');
    }

    /**
     * Manage all job postings (Staff+ — includes inactive/expired).
     */
    #[Route('/manage', name: 'job_board_manage', methods: ['GET'])]
    #[IsGranted('ROLE_STAFF')]
    public function manage(Request $request, JobPostingRepository $repo): Response
    {
        $page  = max(1, $request->query->getInt('page', 1));
        $limit = 20;

        $qb = $repo->createQueryBuilder('j')->orderBy('j.datePosted', 'DESC');
        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);
        $paginator = new Paginator($qb);
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        return $this->render('job_board/manage.html.twig', [
            'jobs' => $paginator,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
        ]);
    }
}
