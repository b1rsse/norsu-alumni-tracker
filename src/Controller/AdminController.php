<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AlumniRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    // ── Users Management ──

    #[Route('/users', name: 'admin_users', methods: ['GET'])]
    public function users(UserRepository $repo): Response
    {
        $users = $repo->findBy([], ['dateRegistered' => 'DESC']);
        $pendingCount = $repo->count(['accountStatus' => 'pending']);

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'pendingCount' => $pendingCount,
        ]);
    }

    #[Route('/users/{id}/approve', name: 'admin_user_approve', methods: ['POST'])]
    public function approveUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('approve' . $user->getId(), $request->request->get('_token'))) {
            $user->setAccountStatus('active');
            $em->flush();
            $this->addFlash('success', $user->getFullName() . ' has been approved.');
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/toggle-status', name: 'admin_user_toggle_status', methods: ['POST'])]
    public function toggleUserStatus(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('toggle' . $user->getId(), $request->request->get('_token'))) {
            $newStatus = $user->getAccountStatus() === 'active' ? 'inactive' : 'active';
            $user->setAccountStatus($newStatus);
            $em->flush();
            $this->addFlash('success', $user->getFullName() . ' status changed to ' . $newStatus . '.');
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/toggle-admin', name: 'admin_user_toggle_admin', methods: ['POST'])]
    public function toggleAdmin(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('admin' . $user->getId(), $request->request->get('_token'))) {
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                $user->setRoles([]);
            } else {
                $user->setRoles(['ROLE_ADMIN']);
            }
            $em->flush();
            $this->addFlash('success', $user->getFullName() . ' role updated.');
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            if ($user === $this->getUser()) {
                $this->addFlash('danger', 'You cannot delete your own account.');
                return $this->redirectToRoute('admin_users');
            }
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'User deleted.');
        }

        return $this->redirectToRoute('admin_users');
    }
}
