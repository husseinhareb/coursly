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
        Request $request,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profilePicFile = $form->get('profilePic')->getData();
            if ($profilePicFile) {
                $originalFilename = pathinfo($profilePicFile->getClientOriginalName(), PATHINFO_FILENAME);
                $subjectCode = 'SUBJECT_CODE';
                $newFilename = $subjectCode . '-' . uniqid() . '.' . $profilePicFile->guessExtension();

                try {
                    $profilePicFile->move(
                        $this->getParameter('profile_pics_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'There was an error uploading your profile picture.');
                }

                $user->setProfilePic($newFilename);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profile updated successfully!');

            return $this->redirectToRoute('app_profile', ['username' => $user->getUsername()]);
        }

        return $this->render('profile/profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
