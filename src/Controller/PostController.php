<?php
// src/Controller/PostController.php
//
// FULL IMPLEMENTATION – every operation (create, edit, delete,
// pin/un-pin, re-order, inline-edit) triggers an AdminAlert so that
// professors always see a moderation notice.

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\{CourseRepository, PostRepository};
use App\Service\AlertManager;
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
    /* ───────────────────────────────
     |  CREATE
     ─────────────────────────────── */
    #[Route('/posts/{id}/{code}/new', name: 'post_new')]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function new(
        int                 $id,
        string              $code,
        Request             $request,
        CourseRepository    $courseRepo,
        PostRepository      $postRepo,
        ManagerRegistry     $doctrine,
        AlertManager        $alerts
    ): Response {
        $course = $courseRepo->find($id) ?? throw $this->createNotFoundException();
        if ($course->getCode() !== $code) {
            return $this->redirectToRoute('post_new', ['id'=>$id,'code'=>$course->getCode()], 301);
        }

        $post = (new Post())
            ->setCourse($course)
            ->setAuthor($this->getUser());

        $form = $this->createForm(PostType::class, $post)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* fichier joint ? */
            if ($post->getType()==='file' && ($file=$form->get('attachment')->getData())) {
                $name = uniqid().'.'.$file->guessExtension();
                try {
                    $file->move($this->getParameter('post_files_directory'), $name);
                    $post->setFilePath($name);
                } catch (FileException) {
                    $this->addFlash('error', 'File upload failed');
                }
            }

            $post->setPosition($postRepo->findMaxPositionForCourse($course)+1);

            $em=$doctrine->getManager();
            $em->persist($post);
            $alerts->raise($this->getUser(),'created',$course,$post);
            $em->flush();

            return $this->redirectToRoute('courses_show',[
                'id'=>$course->getId(),'code'=>$course->getCode()
            ]);
        }

        return $this->render('posts/new.html.twig',[
            'form'=>$form->createView(),
            'course'=>$course,
        ]);
    }

    /* ───────────────────────────────
     |  EDIT (Form)
     ─────────────────────────────── */
    #[Route('/posts/{id}/edit', name: 'post_edit')]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function edit(
        Post               $post,
        Request            $request,
        ManagerRegistry    $doctrine,
        AlertManager       $alerts
    ): Response {
        $form = $this->createForm(PostType::class, $post)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($post->getType()==='file' && ($file=$form->get('attachment')->getData())) {
                $name = uniqid().'.'.$file->guessExtension();
                try {
                    $file->move($this->getParameter('post_files_directory'),$name);
                    $post->setFilePath($name);
                } catch (FileException) {
                    $this->addFlash('error','File upload failed');
                }
            }

            $doctrine->getManager()->flush();
            $alerts->raise($this->getUser(),'updated',$post->getCourse(),$post);
            $doctrine->getManager()->flush();

            return $this->redirectToRoute('courses_show',[
                'id'=>$post->getCourse()->getId(),
                'code'=>$post->getCourse()->getCode()
            ]);
        }

        return $this->render('posts/edit.html.twig',[
            'form'=>$form->createView(),
            'post'=>$post,
            'course'=>$post->getCourse(),
        ]);
    }

    /* ───────────────────────────────
     |  INLINE-EDIT (AJAX)
     ─────────────────────────────── */
    #[Route('/posts/{id}/inline-edit', name: 'post_inline_edit', methods:['POST'])]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function inlineEdit(
        Post            $post,
        Request         $request,
        ManagerRegistry $doctrine,
        AlertManager    $alerts
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        if (isset($data['title']))   $post->setTitle($data['title']);
        if (isset($data['content'])) $post->setContent($data['content']);
        $doctrine->getManager()->flush();
        $alerts->raise($this->getUser(),'updated',$post->getCourse(),$post);
        $doctrine->getManager()->flush();

        return new JsonResponse([
            'id'=>$post->getId(),
            'title'=>$post->getTitle(),
            'content'=>$post->getContent(),
        ]);
    }

    /* ───────────────────────────────
     |  TOGGLE PIN
     ─────────────────────────────── */
    #[Route('/posts/{id}/toggle-pin', name: 'post_toggle_pin', methods:['POST'])]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function togglePin(
        Post             $post,
        ManagerRegistry  $doctrine,
        AlertManager     $alerts
    ): JsonResponse {
        $post->setIsPinned(!$post->isPinned(), $this->getUser());
        $doctrine->getManager()->flush();
        $alerts->raise($this->getUser(),'updated',$post->getCourse(),$post);
        $doctrine->getManager()->flush();
        return new JsonResponse(['pinned'=>$post->isPinned()]);
    }

    /* ───────────────────────────────
     |  MOVE UP / DOWN
     ─────────────────────────────── */
    #[Route('/posts/{id}/move-up',   name:'post_move_up',   methods:['POST'])]
    #[Route('/posts/{id}/move-down', name:'post_move_down', methods:['POST'])]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function move(
        Post               $post,
        Request            $request,
        ManagerRegistry    $doctrine,
        AlertManager       $alerts
    ): JsonResponse {
        $delta = $request->attributes->get('_route')==='post_move_up' ? -1 : +1;
        $em    = $doctrine->getManager();
        $repo  = $em->getRepository(Post::class);

        $neighbor = $repo->findOneBy([
            'course'=>$post->getCourse(),
            'position'=>$post->getPosition()+$delta
        ]);

        if (!$neighbor) {
            return new JsonResponse(['error'=>'boundary'],400);
        }

        $neighbor->setPosition($post->getPosition());
        $post->setPosition($post->getPosition()+$delta);
        $em->flush();
        $alerts->raise($this->getUser(),'updated',$post->getCourse(),$post);
        $em->flush();

        $order = $repo->findBy(['course'=>$post->getCourse()],['position'=>'ASC']);
        return new JsonResponse([
            'success'=>true,
            'order'=>array_map(fn(Post $p)=>['id'=>$p->getId(),'position'=>$p->getPosition()],$order)
        ]);
    }

    /* ───────────────────────────────
     |  DELETE
     ─────────────────────────────── */
    #[Route('/posts/{id}/delete', name:'post_delete', methods:['DELETE'])]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR')")]
    public function delete(
        Post               $post,
        ManagerRegistry    $doctrine,
        AlertManager       $alerts
    ): JsonResponse {
        $em = $doctrine->getManager();
        $alerts->raise($this->getUser(),'deleted',$post->getCourse(),$post);
        $em->remove($post);
        $em->flush();
        return new JsonResponse(['success'=>true]);
    }

    /**
     * @Route("/posts/{id}/download", name="post_download", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_PROFESSOR') or post.getType() === 'file'")
     */
    public function download(Post $post): BinaryFileResponse
    {
        if ($post->getType() !== 'file' || !$post->getFilePath()) {
            throw $this->createNotFoundException('No file attached to this post.');
        }

        $uploadDir = $this->getParameter('post_files_directory');
        $filePath  = $uploadDir . '/' . $post->getFilePath();

        $response = new BinaryFileResponse($filePath);
        // force download and use the filename as label
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            // if you have $post->getOriginalFilename(), use that; otherwise, extract from path:
            $post->getOriginalFilename() ?? basename($filePath)
        );

        return $response;
    }

}
