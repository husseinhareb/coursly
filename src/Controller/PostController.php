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
    /**
     * Using requirements to limit the code parameter to alphanumeric, underscore, and dash characters.
     */
    #[Route(
        '/courses/{id}/{code}/posts/new', 
        name: 'post_new', 
        requirements: ['code' => '[A-Za-z0-9_-]+']
    )]
    public function new(
        int $id,
        string $code,
        Request $request,
        CourseRepository $courseRepository,
        ManagerRegistry $doctrine
    ): Response {
        // Retrieve the course by id
        $course = $courseRepository->find($id);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }
        
        // Verify that the provided code matches the course code
        if ($course->getCode() !== $code) {
            throw $this->createNotFoundException('Invalid course code');
        }

        // Create a new post and assign the course and (if available) its author
        $post = new Post();
        $post->setCourse($course);
        if ($this->getUser()) {
            $post->setAuthor($this->getUser());
        }
        
        // Create and handle the post form
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // If the post is a file type and an attachment is provided, handle file upload
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
                        // Optionally, add error handling logic here (e.g. flash error message)
                        $this->addFlash('error', 'Failed to upload file.');
                    }
                }
            }
            
            // Save the new post
            $entityManager = $doctrine->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            
            // Redirect back to the course page upon successful creation
            return $this->redirectToRoute('courses_show', [
                'id' => $course->getId(),
                'code' => $course->getCode()
            ]);
        }
        
        // Render the form for creating a new post
        return $this->render('posts/new.html.twig', [
            'form' => $form->createView(),
            'course' => $course,
        ]);
    }
}
