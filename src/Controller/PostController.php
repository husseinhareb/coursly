<?php
// src/Controller/PostController.php
namespace App\Controller;

use App\Entity\Post;
use App\Entity\Course;
use App\Form\PostType;
use App\Repository\CourseRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/courses/{id}/posts/new', name: 'post_new')]
    public function new(
        int $id,
        Request $request,
        CourseRepository $courseRepository,
        ManagerRegistry $doctrine
    ): Response {
         $course = $courseRepository->find($id);
         if (!$course) {
              throw $this->createNotFoundException('Course not found');
         }
         
         $post = new Post();
         $post->setCourse($course);
         
         if ($this->getUser()) {
             $post->setAuthor($this->getUser());
         }
         
         $form = $this->createForm(PostType::class, $post);
         $form->handleRequest($request);
         
         if ($form->isSubmitted() && $form->isValid()) {
              if ($post->getType() === 'file') {
                  $attachment = $form->get('attachment')->getData();
                  if ($attachment) {
                      $newFilename = uniqid() . '.' . $attachment->guessExtension();
                      try {
                          $attachment->move(
                              $this->getParameter('posts_files_directory'),
                              $newFilename
                          );
                          $post->setFilePath($newFilename);
                      } catch (FileException $e) {
                      }
                  }
              }
              
              $entityManager = $doctrine->getManager();
              $entityManager->persist($post);
              $entityManager->flush();
              
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
