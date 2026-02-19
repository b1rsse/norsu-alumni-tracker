<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $firstName = trim($request->request->get('firstName', ''));
            $lastName  = trim($request->request->get('lastName', ''));
            $email     = trim($request->request->get('email', ''));

            if ($firstName === '' || $lastName === '' || $email === '') {
                $this->addFlash('danger', 'First name, last name, and email are required.');
                return $this->redirectToRoute('app_profile_edit');
            }

            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);

            // Handle password change (optional)
            $newPassword = $request->request->get('newPassword', '');
            $confirmPassword = $request->request->get('confirmPassword', '');

            if ($newPassword !== '') {
                if (strlen($newPassword) < 6) {
                    $this->addFlash('danger', 'Password must be at least 6 characters.');
                    return $this->redirectToRoute('app_profile_edit');
                }
                if ($newPassword !== $confirmPassword) {
                    $this->addFlash('danger', 'Passwords do not match.');
                    return $this->redirectToRoute('app_profile_edit');
                }
                $user->setPassword($hasher->hashPassword($user, $newPassword));
            }

            $em->flush();
            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
        ]);
    }
}
