<?php
// src/Controller/CourseController.php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\UserCourseAccess;
use App\Form\CourseType;
use App\Repository\AdminAlertRepository;
use App\Repository\PostRepository;
use App\Repository\CourseRepository;
use App\Repository\UserCourseAccessRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CourseController extends AbstractController
{
    // ───────────────────────────────
    //  LISTE / TABLEAU DE BORD
    // ───────────────────────────────
    #[Route('/courses', name: 'courses_index')]
    public function index(
        CourseRepository $courseRepo,
        PostRepository   $postRepo,
        ManagerRegistry  $doctrine
    ): Response {
        $user = $this->getUser();
        $em   = $doctrine->getManager();
    
        // 1) Récupérer les UE visibles pour l’utilisateur
        if ($this->isGranted('ROLE_ADMIN')) {
            $courses = $courseRepo->findAll();
        } else {
            // Suppose you add this helper in CourseRepository:
            // public function findByUser(User $user): array { … }
            $courses = $courseRepo->findByUser($user);
        }
    
        // 2) Assurer une couleur de fond sur chaque carte
        $colors = [];
        foreach ($courses as $course) {
            if (!$course->getBackground()) {
                $course->setBackground($this->generateRandomColor());
                $em->persist($course);
            }
            $colors[] = $course->getBackground();
        }
        $em->flush();
    
        // 3) Construire le fil d’actualité (10 derniers posts de ses UE)
        $recentActivities = $postRepo->findRecentByUser($user, 10);
    
        // 4) Rendu
        return $this->render('courses/courses.html.twig', [
            'courses'          => $courses,
            'colors'           => $colors,
            'recentActivities' => $recentActivities,
        ]);
    }
    

    // ───────────────────────────────
    //  PAGE D’UNE UE
    // ───────────────────────────────
    #[Route(
        '/courses/{id}/{code}',
        name: 'courses_show',
        requirements: ['id' => '\d+', 'code' => '.+']
    )]
    public function show(
        int                    $id,
        string                 $code,
        CourseRepository       $courseRepo,
        PostRepository         $postRepo,
        AdminAlertRepository   $alertRepo,
        ManagerRegistry        $doctrine
    ): Response {
        /* 1️⃣  ───────── Validation UE ───────── */
        $course = $courseRepo->find($id);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }
        if ($course->getCode() !== $code) {
            // slug canonicalisation
            return $this->redirectToRoute('courses_show', [
                'id'   => $id,
                'code' => $course->getCode(),
            ], 301);
        }

        /* 2️⃣  ───────── Log accès utilisateur ───────── */
        if ($user = $this->getUser()) {
            $em     = $doctrine->getManager();
            $ucRepo = $em->getRepository(UserCourseAccess::class);

            $access = $ucRepo->findOneBy(['user' => $user, 'course' => $course])
                   ?? (new UserCourseAccess())->setUser($user)->setCourse($course);

            $access->setAccessedAt(new \DateTime());
            $em->persist($access);
            $em->flush();
        }

        /* 3️⃣  ───────── Posts (pinned puis position ASC) ───────── */
        $posts = $postRepo->createQueryBuilder('p')
            ->andWhere('p.course = :course')
            ->setParameter('course', $course)
            ->orderBy('p.isPinned', 'DESC')
            ->addOrderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();

        /* 4️⃣  ───────── Alertes admin non lues ───────── */
        $unreadAlerts = [];
        $alertMap     = [];                       // post-id → AdminAlert
        if ($user = $this->getUser()) {
            $unreadAlerts = $alertRepo->findUnreadByCourseAndUser($course, $user);
        
            foreach ($unreadAlerts as $alert) {
                // un seul drapeau par post : on conserve la plus récente
                $pid = $alert->getPost()?->getId();
                if ($pid && !isset($alertMap[$pid])) {
                    $alertMap[$pid] = $alert;
                }
            }
        }

        /* 5️⃣  ───────── Render ───────── */
        return $this->render('courses/course.html.twig', [
            'course'       => $course,
            'posts'        => $posts,
            'unreadAlerts' => $unreadAlerts,
            'alertMap'     => $alertMap,
        ]);
        
    }

    // ───────────────────────────────
    //  LISTE DES INSCRITS
    // ───────────────────────────────
    #[Route(
        '/courses/enrolled/{id}/{code}',
        name: 'courses_enrolled',
        requirements: ['id' => '\d+', 'code' => '.+']
    )]
    public function enrolled(
        int                        $id,
        string                     $code,
        CourseRepository           $repo,
        UserCourseAccessRepository $accessRepo
    ): Response {
        $course = $repo->find($id);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }
        if ($course->getCode() !== $code) {
            return $this->redirectToRoute('courses_enrolled', [
                'id'   => $id,
                'code' => $course->getCode(),
            ], 301);
        }

        // dern. accès de chaque user
        $rows         = $accessRepo->findLatestAccessByCourse($course);
        $lastAccessed = [];
        foreach ($rows as $r) {
            $lastAccessed[$r['user_id']] = $r['lastAccessed'];
        }

        return $this->render('courses/enrolled.html.twig', [
            'course'       => $course,
            'users'        => $course->getUsers(),
            'lastAccessed' => $lastAccessed,
        ]);
    }

    // ───────────────────────────────
    //  AJAX Search (auto-complete)
    // ───────────────────────────────
    #[Route('/search-courses', name: 'courses_search', methods: ['GET'])]
    public function searchCourses(
        Request          $request,
        CourseRepository $repo
    ): JsonResponse {
        $term = trim((string) $request->query->get('q', ''));
        if ($term === '') {
            return new JsonResponse([]);
        }

        $found = $repo->searchCourses($term);
        $data  = array_map(fn (Course $c) => [
            'id'    => $c->getId(),
            'title' => $c->getTitle(),
            'code'  => $c->getCode(),
        ], $found);

        return new JsonResponse($data);
    }

    // ───────────────────────────────
    //  CRÉATION d’UE (admin only)
    // ───────────────────────────────
    #[Route('/courses/new', name: 'courses_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request          $request,
        ManagerRegistry  $doctrine
    ): Response {
        $course = new Course();
        $course->setCreatedAt(new \DateTimeImmutable())
               ->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CourseType::class, $course)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // upload image éventuelle
            if ($img = $form->get('image')->getData()) {
                $fn = uniqid('', true) . '.' . $img->guessExtension();
                try {
                    $img->move($this->getParameter('course_pics_directory'), $fn);
                    $course->setBackground($fn);
                } catch (FileException) {
                    // flash ou log si besoin
                }
            }

            $em = $doctrine->getManager();
            $em->persist($course);
            $em->flush();

            return $this->redirectToRoute('courses_index');
        }

        return $this->render('courses/new.html.twig', [
            'form'   => $form->createView(),
            'course' => $course,
        ]);
    }

    // ───────────────────────────────
    //  ÉDITION d’UE (admin only)
    // ───────────────────────────────
    #[Route('/courses/edit/{id}', name: 'courses_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        int              $id,
        Request          $request,
        CourseRepository $repo,
        ManagerRegistry  $doctrine
    ): Response {
        $course = $repo->find($id) ?? throw $this->createNotFoundException('Course not found');

        $form = $this->createForm(CourseType::class, $course)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($img = $form->get('image')->getData()) {
                $fn = uniqid('', true) . '.' . $img->guessExtension();
                try {
                    $img->move($this->getParameter('course_pics_directory'), $fn);
                    $course->setBackground($fn);
                } catch (FileException) {
                    // silently ignore or flash
                }
            }
            $doctrine->getManager()->flush();

            return $this->redirectToRoute('courses_show', [
                'id'   => $course->getId(),
                'code' => $course->getCode(),
            ]);
        }

        return $this->render('courses/edit.html.twig', [
            'form'   => $form->createView(),
            'course' => $course,
        ]);
    }

    // ───────────────────────────────
    //  Utils
    // ───────────────────────────────
    private function generateRandomColor(): string
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}
