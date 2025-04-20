<?php
namespace App\Controller;

use App\Entity\Course;
use App\Entity\UserCourseAccess;
use App\Form\CourseType;
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
    #[Route('/courses', name: 'courses_index')]
    public function index(
        Request $request,
        CourseRepository $repo,
        ManagerRegistry $doctrine
    ): Response {
        $courses = $repo->findAll();
        $user    = $this->getUser();

        if ($user && !$this->isGranted('ROLE_ADMIN')) {
            $courses = array_filter($courses, fn(Course $c) => $c->getUsers()->contains($user));
        }

        $colors = [];
        $em     = $doctrine->getManager();
        foreach ($courses as $course) {
            if (empty($course->getBackground())) {
                $course->setBackground($this->generateRandomColor());
                $em->persist($course);
            }
            $colors[] = $course->getBackground();
        }
        $em->flush();

        return $this->render('courses/courses.html.twig', [
            'courses' => $courses,
            'colors'  => $colors,
        ]);
    }

    #[Route('/courses/{id}/{code}', name: 'courses_show', requirements: ['id' => '\d+', 'code' => '.+'])]
    public function show(
        int $id,
        string $code,
        CourseRepository $repo,
        ManagerRegistry $doctrine
    ): Response {
        $course = $repo->find($id);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }
        if ($course->getCode() !== $code) {
            return $this->redirectToRoute('courses_show', [
                'id'   => $id,
                'code' => $course->getCode()
            ], 301);
        }

        if ($user = $this->getUser()) {
            $em   = $doctrine->getManager();
            $iar  = $em->getRepository(UserCourseAccess::class);
            $access = $iar->findOneBy(['user' => $user, 'course' => $course]);
            if (!$access) {
                $access = new UserCourseAccess();
                $access->setUser($user)->setCourse($course);
            }
            $access->setAccessedAt(new \DateTime());
            $em->persist($access);
            $em->flush();
        }

        return $this->render('courses/course.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/courses/enrolled/{id}/{code}', name: 'courses_enrolled', requirements: ['id' => '\d+', 'code' => '.+'])]
    public function enrolled(
        int $id,
        string $code,
        CourseRepository $repo,
        UserCourseAccessRepository $accessRepo
    ): Response {
        $course = $repo->find($id);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }
        if ($course->getCode() !== $code) {
            return $this->redirectToRoute('courses_enrolled', [
                'id'   => $id,
                'code' => $course->getCode()
            ], 301);
        }

        $users        = $course->getUsers();
        $rows         = $accessRepo->findLatestAccessByCourse($course);
        $lastAccessed = [];
        foreach ($rows as $r) {
            $lastAccessed[$r['user_id']] = $r['lastAccessed'];
        }

        return $this->render('courses/enrolled.html.twig', [
            'course'       => $course,
            'users'        => $users,
            'lastAccessed' => $lastAccessed,
        ]);
    }

    #[Route('/search-courses', name: 'courses_search', methods: ['GET'])]
    public function searchCourses(Request $request, CourseRepository $repo): JsonResponse
    {
        $term = $request->query->get('q', '');
        if ('' === $term) {
            return new JsonResponse([]);
        }

        $found = $repo->searchCourses($term);
        $data  = array_map(fn(Course $c) => [
            'id'    => $c->getId(),
            'title' => $c->getTitle(),
            'code'  => $c->getCode(),
        ], $found);

        return new JsonResponse($data);
    }

    #[Route('/courses/new', name: 'courses_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $course = new Course();
        $course->setCreatedAt(new \DateTimeImmutable())
               ->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($img = $form->get('image')->getData()) {
                $fn = uniqid() . '.' . $img->guessExtension();
                try {
                    $img->move($this->getParameter('course_pics_directory'), $fn);
                    $course->setBackground($fn);
                } catch (FileException $e) {
                    // optional: flash or log
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

    #[Route('/courses/edit/{id}', name: 'courses_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        int $id,
        Request $request,
        CourseRepository $repo,
        ManagerRegistry $doctrine
    ): Response {
        $course = $repo->find($id);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($img = $form->get('image')->getData()) {
                $fn = uniqid() . '.' . $img->guessExtension();
                try {
                    $img->move($this->getParameter('course_pics_directory'), $fn);
                    $course->setBackground($fn);
                } catch (FileException $e) {
                    // optional
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

    private function generateRandomColor(): string
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}
