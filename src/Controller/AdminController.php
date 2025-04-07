<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Enrollment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\CourseRepository;
use App\Form\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdminController extends AbstractController
{
    #[Route('/admin/{username}/edit-user/{id}', name: 'admin_edit_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function editUser(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        int $id
    ): Response {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException("User not found");
        }

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
                } catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {
                    $this->addFlash('error', 'There was an error uploading the file.');
                }
                $user->setProfilePic($newFilename);
            }

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'User profile updated successfully!');

            return $this->redirectToRoute('admin_edit_user', [
                'username' => $this->getUser()->getUsername(),
                'id' => $user->getId()
            ]);
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

    #[Route('/admin/{username}/manage-enrollments/{id}', name: 'admin_manage_enrollments')]
    #[IsGranted('ROLE_ADMIN')]
    public function manageEnrollments(User $user, CourseRepository $courseRepo): Response
    {
        $courses = $courseRepo->findAll();

        return $this->render('admin/edit_enrollments.html.twig', [
            'user' => $user,
            'courses' => $courses,
        ]);
    }

    #[Route('/admin/{username}/add-enrollment/{id}/{courseId}', name: 'admin_add_enrollment', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addEnrollment(
        Request $request,
        User $user,
        int $courseId,
        EntityManagerInterface $em,
        CourseRepository $courseRepo
    ): Response {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('warning', 'Admins cannot enroll in courses.');
            return $this->redirectToRoute('admin_manage_enrollments', [
                'username' => $this->getUser()->getUsername(),
                'id' => $user->getId()
            ]);
        }

        foreach ($user->getEnrollments() as $enrollment) {
            if ($enrollment->getCourse()->getId() === $courseId) {
                $this->addFlash('info', 'Course is already enrolled.');
                return $this->redirectToRoute('admin_manage_enrollments', [
                    'username' => $this->getUser()->getUsername(),
                    'id' => $user->getId()
                ]);
            }
        }

        $course = $courseRepo->find($courseId);
        if (!$course) {
            $this->addFlash('warning', 'Course not found.');
            return $this->redirectToRoute('admin_manage_enrollments', [
                'username' => $this->getUser()->getUsername(),
                'id' => $user->getId()
            ]);
        }

        $enrollment = new Enrollment();
        $enrollment->setUser($user);
        $enrollment->setCourse($course);
        $em->persist($enrollment);
        $em->flush();

        $this->addFlash('success', 'Course added successfully.');
        return $this->redirectToRoute('admin_manage_enrollments', [
            'username' => $this->getUser()->getUsername(),
            'id' => $user->getId()
        ]);
    }

    #[Route('/admin/{username}/remove-enrollment/{id}/{courseId}', name: 'admin_remove_enrollment', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function removeEnrollment(
        Request $request,
        User $user,
        int $courseId,
        EntityManagerInterface $em
    ): Response {
        $found = false;
        foreach ($user->getEnrollments() as $enrollment) {
            if ($enrollment->getCourse()->getId() === $courseId) {
                $em->remove($enrollment);
                $user->removeEnrollment($enrollment);
                $found = true;
                break;
            }
        }
        if ($found) {
            $em->flush();
            $this->addFlash('success', 'Enrollment removed successfully.');
        } else {
            $this->addFlash('warning', 'Enrollment not found.');
        }
        return $this->redirectToRoute('admin_manage_enrollments', [
            'username' => $this->getUser()->getUsername(),
            'id' => $user->getId()
        ]);
    }
}
