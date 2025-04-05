<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\UserRepository;
use App\Form\ProfileType;

class AdminController extends AbstractController
{
    #[Route('/admin/{username}/edit-user/{id}', name: 'admin_edit_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function editUser(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        UserRepository $userRepository,
        int $id
    ): Response {
        // Fetch the user to be edited
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException("User not found");
        }

        // Create the same ProfileType form
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload for profile picture if needed
            $profilePicFile = $form->get('profilePic')->getData();
            if ($profilePicFile) {
                $originalFilename = pathinfo($profilePicFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePicFile->guessExtension();

                try {
                    $profilePicFile->move(
                        $this->getParameter('profile_pics_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'There was an error uploading the file.');
                }
                $user->setProfilePic($newFilename);
            }

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'User profile updated successfully!');

            // Redirect back to this edit page (or to a user list, as you prefer)
            return $this->redirectToRoute('admin_edit_user', ['id' => $user->getId()]);
        }

        return $this->render('profile/profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/{username}/edit-users', name: 'app_edit_users')]
    #[IsGranted('ROLE_ADMIN')]
    public function editUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/edit_users.html.twig', [
            'users' => $users,
        ]);
    }

}
