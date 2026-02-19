<?php

namespace App\Controller;

use App\Entity\Alumni;
use App\Form\AlumniType;
use App\Repository\AlumniRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/alumni')]
class AlumniController extends AbstractController
{
    #[Route('/', name: 'alumni_index', methods: ['GET'])]
    public function index(Request $request, AlumniRepository $repo): Response
    {
        $batch = $request->query->get('batch');
        $course = $request->query->get('course');

        $qb = $repo->createQueryBuilder('a');

        if ($batch !== null && $batch !== '') {
            $qb->andWhere('a.batchYear = :batch')->setParameter('batch', (int) $batch);
        }

        if ($course !== null && $course !== '') {
            $qb->andWhere('a.course LIKE :course')->setParameter('course', '%'.$course.'%');
        }

        $alumnis = $qb->orderBy('a.lastName', 'ASC')->getQuery()->getResult();

        return $this->render('alumni/index.html.twig', [
            'alumnis' => $alumnis,
            'search_batch' => $batch,
            'search_course' => $course,
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

            return $this->redirectToRoute('alumni_index');
        }

        return $this->render('alumni/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Add Alumni',
        ]);
    }

    #[Route('/{id}/edit', name: 'alumni_edit', methods: ['GET', 'POST'])]
    public function edit(Alumni $alumni, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AlumniType::class, $alumni);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('alumni_index');
        }

        return $this->render('alumni/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Edit Alumni',
        ]);
    }

    #[Route('/{id}', name: 'alumni_show', methods: ['GET'])]
    public function show(Alumni $alumni): Response
    {
        return $this->render('alumni/show.html.twig', [
            'alumni' => $alumni,
        ]);
    }
}
