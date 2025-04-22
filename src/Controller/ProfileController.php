<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/{username}/profile', name: 'app_profile')]
    public function profile(
        string                 $username,
        Request                $request,
        EntityManagerInterface $em
    ): Response {
        // ① l’utilisateur affiché (profil consulté)
        $viewed = $em->getRepository(User::class)
                    ->findOneBy(['username' => $username])
            ?? throw $this->createNotFoundException();

        // ② l’utilisateur courant (peut être null)
        $current = $this->getUser();

        // ③ permission : seul le propriétaire ou un admin voit/édite
        if (!$current || ($current !== $viewed && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createAccessDeniedException();
        }

        /* ---------- édition uniquement si on affiche SON propre profil ---------- */
        $form = $this->createForm(ProfileType::class, $viewed, [
            'disabled' => $current !== $viewed,   // lecture seule quand ce n’est pas le tien
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($file = $form->get('profilePic')->getData()) {
                $newName = 'PP-' . uniqid() . '.' . $file->guessExtension();
                try {
                    $file->move($this->getParameter('profile_pics_directory'), $newName);
                    $viewed->setProfilePic($newName);
                } catch (FileException) {
                    $this->addFlash('error', 'profile.upload_error');
                }
            }
            $em->flush();
            $this->addFlash('success', 'profile.updated');
            return $this->redirectToRoute('app_profile', ['username' => $viewed->getUsername()]);
        }

        return $this->render('profile/profile.html.twig', [
            'user' => $viewed,      // l’utilisateur affiché
            'form' => $form->createView(),
        ]);
    }

}
