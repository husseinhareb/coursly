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
    Response,
    JsonResponse
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
        CourseRepository $courseRepo,
        ManagerRegistry $doctrine,
        PostRepository $postRepo
    ): Response {
        $course = $courseRepo->find($id);
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
            // Handle file if needed
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

            // Determine the next position
            $maxPos = $postRepo->findMaxPositionForCourse($course);
            $post->setPosition($maxPos + 1);

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
        PostRepository $postRepo,
        ManagerRegistry $doctrine
    ): Response {
        $post = $postRepo->find($id);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }
        // Redirect if URL params don’t match
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
                    }
                }
            }

            $doctrine->getManager()->flush();

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

    #[Route('/posts/{id}/toggle-pin', name: 'post_toggle_pin', methods: ['POST'])]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function togglePin(int $id, ManagerRegistry $doctrine): JsonResponse
    {
        $em   = $doctrine->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        if (!$post) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $newState = !$post->isPinned();
        $post->setIsPinned($newState, $this->getUser());
        $em->flush();

        return new JsonResponse(['pinned' => $newState]);
    }

    #[Route('/posts/{id}/move-up', name: 'post_move_up', methods: ['POST'])]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function moveUp(int $id, ManagerRegistry $doctrine): JsonResponse
    {
        $em   = $doctrine->getManager();
        $repo = $em->getRepository(Post::class);
        $post = $repo->find($id);
        if (!$post) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $course = $post->getCourse();
        $pos    = $post->getPosition();
        if ($pos <= 1) {
            return new JsonResponse(['error' => 'Already at top'], 400);
        }

        // find the post immediately above
        $neighbor = $repo->findOneBy([
            'course'   => $course,
            'position' => $pos - 1,
        ]);

        if (!$neighbor) {
            return new JsonResponse(['error' => 'Cannot move up'], 400);
        }

        // swap
        $post->setPosition($pos - 1);
        $neighbor->setPosition($pos);
        $em->flush();

        // rebuild the full order array
        $ordered = $repo->findBy(['course' => $course], ['position' => 'ASC']);
        $order = array_map(fn(Post $p) => [
            'id'       => $p->getId(),
            'position' => $p->getPosition(),
        ], $ordered);

        return new JsonResponse(['success' => true, 'order' => $order]);
    }

    #[Route('/posts/{id}/move-down', name: 'post_move_down', methods: ['POST'])]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function moveDown(int $id, ManagerRegistry $doctrine): JsonResponse
    {
        $em   = $doctrine->getManager();
        $repo = $em->getRepository(Post::class);
        $post = $repo->find($id);
        if (!$post) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $course = $post->getCourse();
        $pos    = $post->getPosition();

        // find maximum position
        $max = (int)$repo->createQueryBuilder('p')
            ->select('MAX(p.position)')
            ->andWhere('p.course = :course')
            ->setParameter('course', $course)
            ->getQuery()
            ->getSingleScalarResult();

        if ($pos >= $max) {
            return new JsonResponse(['error' => 'Already at bottom'], 400);
        }

        $neighbor = $repo->findOneBy([
            'course'   => $course,
            'position' => $pos + 1,
        ]);

        if (!$neighbor) {
            return new JsonResponse(['error' => 'Cannot move down'], 400);
        }

        // swap
        $post->setPosition($pos + 1);
        $neighbor->setPosition($pos);
        $em->flush();

        // rebuild the full order array
        $ordered = $repo->findBy(['course' => $course], ['position' => 'ASC']);
        $order = array_map(fn(Post $p) => [
            'id'       => $p->getId(),
            'position' => $p->getPosition(),
        ], $ordered);

        return new JsonResponse(['success' => true, 'order' => $order]);
    }



    #[Route('/posts/{id}/inline-edit', name: 'post_inline_edit', methods: ['POST'])]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function inlineEdit(int $id, Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $em   = $doctrine->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        if (!$post) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        if (isset($payload['title'])) {
            $post->setTitle($payload['title']);
        }
        if (isset($payload['content'])) {
            $post->setContent($payload['content']);
        }

        $em->flush();

        return new JsonResponse([
            'id'      => $post->getId(),
            'title'   => $post->getTitle(),
            'content' => $post->getContent(),
        ]);
    }

    #[Route('/posts/{id}/delete', name: 'post_delete', methods: ['DELETE'])]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function delete(int $id, ManagerRegistry $doctrine): JsonResponse
    {
        $em   = $doctrine->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        if (!$post) {
            return new JsonResponse(['error'=>'Not found'], 404);
        }

        $em->remove($post);
        $em->flush();

        return new JsonResponse(['success'=>true]);
    }

    private function buildOrder($course): array
    {
        $all = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findBy(['course' => $course], ['position'=>'ASC']);

        return array_map(fn(Post $p) => [
            'id'       => $p->getId(),
            'position' => $p->getPosition()
        ], $all);
    }
}
