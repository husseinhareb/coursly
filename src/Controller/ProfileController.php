<?php

namespace App\Controller;

use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends AbstractController
{
    #[Route('/profile/edit', name: 'profile_edit')]
    public function editProfile(
        Request $request, 
        EntityManagerInterface $em, 
        SluggerInterface $slugger
    ): Response {
        // Get the currently logged-in user
        $user = $this->getUser();

        // Create and handle the form
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the uploaded file from the form (this field is unmapped)
            $profilePicFile = $form->get('profilePic')->getData();

            if ($profilePicFile) {
                // Generate a safe filename
                $originalFilename = pathinfo($profilePicFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePicFile->guessExtension();

                // Move the file to the directory where profile pics are stored
                try {
                    $profilePicFile->move(
                        $this->getParameter('profile_pics_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle exception if something goes wrong during file upload
                    $this->addFlash('error', 'There was an error uploading your profile picture.');
                }

                // Update the user entity with the new filename
                $user->setProfilePic($newFilename);
            }

            // Persist the updated user entity
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profile updated successfully!');

            // Redirect back to the profile edit page to show the updated picture
            return $this->redirectToRoute('profile_edit');
        }

        return $this->render('profile/profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
