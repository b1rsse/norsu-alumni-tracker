<?php

namespace App\Controller;

use App\Entity\Alumni;
use App\Form\AlumniType;
use App\Repository\AlumniRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/alumni')]
class AlumniController extends AbstractController
{
    #[Route('/', name: 'alumni_index', methods: ['GET'])]
    public function index(Request $request, AlumniRepository $repo): Response
    {
        $search   = $request->query->get('q', '');
        $course   = $request->query->get('course', '');
        $year     = $request->query->get('year', '');
        $status   = $request->query->get('status', '');
        $province = $request->query->get('province', '');

        $qb = $repo->createQueryBuilder('a');

        if ($search !== '') {
            $qb->andWhere('a.firstName LIKE :q OR a.lastName LIKE :q OR a.studentNumber LIKE :q OR a.emailAddress LIKE :q')
               ->setParameter('q', '%' . $search . '%');
        }
        if ($course !== '') {
            $qb->andWhere('a.course LIKE :course')->setParameter('course', '%' . $course . '%');
        }
        if ($year !== '') {
            $qb->andWhere('a.yearGraduated = :year')->setParameter('year', (int) $year);
        }
        if ($status !== '') {
            $qb->andWhere('a.employmentStatus = :status')->setParameter('status', $status);
        }
        if ($province !== '') {
            $qb->andWhere('a.province LIKE :province')->setParameter('province', '%' . $province . '%');
        }

        $alumnis = $qb->orderBy('a.lastName', 'ASC')->getQuery()->getResult();

        // Get distinct values for filter dropdowns
        $courses = $repo->createQueryBuilder('a')
            ->select('DISTINCT a.course')->where('a.course IS NOT NULL')
            ->orderBy('a.course', 'ASC')->getQuery()->getSingleColumnResult();

        $years = $repo->createQueryBuilder('a')
            ->select('DISTINCT a.yearGraduated')->where('a.yearGraduated IS NOT NULL')
            ->orderBy('a.yearGraduated', 'DESC')->getQuery()->getSingleColumnResult();

        $provinces = $repo->createQueryBuilder('a')
            ->select('DISTINCT a.province')->where('a.province IS NOT NULL AND a.province != :empty')
            ->setParameter('empty', '')->orderBy('a.province', 'ASC')->getQuery()->getSingleColumnResult();

        return $this->render('alumni/index.html.twig', [
            'alumnis'   => $alumnis,
            'search'    => $search,
            'courses'   => $courses,
            'years'     => $years,
            'provinces' => $provinces,
            'filter_course'   => $course,
            'filter_year'     => $year,
            'filter_status'   => $status,
            'filter_province' => $province,
        ]);
    }

    #[Route('/create', name: 'alumni_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $alumni = new Alumni();
        $form = $this->createForm(AlumniType::class, $alumni);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($alumni);
            $em->flush();
            $this->addFlash('success', 'Alumni record created successfully.');

            return $this->redirectToRoute('alumni_show', ['id' => $alumni->getId()]);
        }

        return $this->render('alumni/form.html.twig', [
            'form'  => $form->createView(),
            'title' => 'Add New Alumni',
            'alumni' => $alumni,
        ]);
    }

    #[Route('/{id}/edit', name: 'alumni_edit', methods: ['GET', 'POST'])]
    public function edit(Alumni $alumni, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AlumniType::class, $alumni);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Alumni record updated successfully.');

            return $this->redirectToRoute('alumni_show', ['id' => $alumni->getId()]);
        }

        return $this->render('alumni/form.html.twig', [
            'form'  => $form->createView(),
            'title' => 'Edit Alumni — ' . $alumni->getFullName(),
            'alumni' => $alumni,
        ]);
    }

    #[Route('/{id}', name: 'alumni_show', methods: ['GET'])]
    public function show(Alumni $alumni): Response
    {
        return $this->render('alumni/show.html.twig', [
            'alumni' => $alumni,
        ]);
    }

    #[Route('/{id}/delete', name: 'alumni_delete', methods: ['POST'])]
    public function delete(Alumni $alumni, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $alumni->getId(), $request->request->get('_token'))) {
            $em->remove($alumni);
            $em->flush();
            $this->addFlash('success', 'Alumni record deleted.');
        }

        return $this->redirectToRoute('alumni_index');
    }
}
