<?php
// src/Controller/PostController.php
namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\CourseRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class PostController extends AbstractController
{
    #[Route('/posts/{id}/{code}/new', name: 'post_new')]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROF')")]
    public function new(
        int $id,
        Request $request,
        CourseRepository $courseRepository,
        ManagerRegistry $doctrine
    ): Response {
         // Find the course by ID
         $course = $courseRepository->find($id);
         if (!$course) {
              throw $this->createNotFoundException('Course not found');
         }
         
         // Create a new Post and associate it with the course
         $post = new Post();
         $post->setCourse($course);
         
         // Optionally set the author as the currently logged-in user
         if ($this->getUser()) {
             $post->setAuthor($this->getUser());
         }
         
         // Create and handle the form
         $form = $this->createForm(PostType::class, $post);
         $form->handleRequest($request);
         
         if ($form->isSubmitted() && $form->isValid()) {
              // If the post type is "file", handle the file upload
              if ($post->getType() === 'file') {
                  $attachment = $form->get('attachment')->getData();
                  if ($attachment) {
                      $newFilename = uniqid() . '.' . $attachment->guessExtension();
                      try {
                          $attachment->move(
                              $this->getParameter('post_files_directory'),
                              $newFilename
                          );
                          $post->setFilePath($newFilename);
                      } catch (FileException $e) {
                          // Add a flash message and re-display the form if there’s an error during file move.
                          $this->addFlash('error', 'File upload error: ' . $e->getMessage());
                          return $this->render('posts/new.html.twig', [
                              'form' => $form->createView(),
                              'course' => $course,
                          ]);
                      }
                  }
              }
              
              $entityManager = $doctrine->getManager();
              $entityManager->persist($post);
              $entityManager->flush();
              
              // Redirect to the course detail page after successful post creation.
              return $this->redirectToRoute('courses_show', [
                  'id' => $course->getId(),
                  'code' => $course->getCode()
              ]);
         }
         
         return $this->render('posts/new.html.twig', [
              'form' => $form->createView(),
              'course' => $course,
         ]);
    }
}
