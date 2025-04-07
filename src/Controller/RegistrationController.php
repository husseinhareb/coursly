<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RegistrationController extends AbstractController
{
    #[Route('/admin/{username}/register', name: 'app_register')]
    #[IsGranted('ROLE_ADMIN')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            if (!$user->getUsername()) {
                $randomNumber = rand(10, 999);
                $generatedUsername = strtolower($user->getFirstName()) . '_' . strtolower($user->getLastName()) . $randomNumber;
                $user->setUsername($generatedUsername);
            }

            $selectedRole = $form->get('role')->getData();
            if ($selectedRole === 'ROLE_ADMIN_PROFESSOR') {
                $user->setRoles(['ROLE_ADMIN', 'ROLE_PROFESSOR']);
            } else {
                $user->setRoles([$selectedRole]);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
