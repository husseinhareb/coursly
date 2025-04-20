<?php
// src/Controller/PostController.php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\CourseRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class PostController extends AbstractController
{
    #[Route('/posts/{id}/{code}/new', name: 'post_new')]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
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
                            $this->getParameter('post_files_directory'),
                            $newFilename
                        );
                        $post->setFilePath($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'File upload error: ' . $e->getMessage());
                        return $this->render('posts/new.html.twig', [
                            'form'   => $form->createView(),
                            'course' => $course,
                        ]);
                    }
                }
            }

            $em = $doctrine->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('courses_show', [
                'id'   => $course->getId(),
                'code' => $course->getCode(),
            ]);
        }

        return $this->render('posts/new.html.twig', [
            'form'   => $form->createView(),
            'course' => $course,
        ]);
    }

    #[Route('/posts/{id}/{code}/edit', name: 'post_edit')]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function edit(
        int $id,
        string $code,
        Request $request,
        PostRepository $postRepository,
        ManagerRegistry $doctrine
    ): Response {
        $post = $postRepository->find($id);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }
        // verify course/code match
        if ($post->getCourse()->getId() !== $id || $post->getCourse()->getCode() !== $code) {
            return $this->redirectToRoute('post_edit', [
                'id'   => $post->getCourse()->getId(),
                'code' => $post->getCourse()->getCode(),
            ], 301);
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
                            $this->getParameter('post_files_directory'),
                            $newFilename
                        );
                        $post->setFilePath($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'File upload error: ' . $e->getMessage());
                        // fall through to re-render form
                    }
                }
            }

            $em = $doctrine->getManager();
            $em->flush();

            return $this->redirectToRoute('courses_show', [
                'id'   => $post->getCourse()->getId(),
                'code' => $post->getCourse()->getCode(),
            ]);
        }

        return $this->render('posts/edit.html.twig', [
            'form'   => $form->createView(),
            'post'   => $post,
            'course' => $post->getCourse(),
        ]);
    }
}
