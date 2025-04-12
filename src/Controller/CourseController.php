<?php
// src/Controller/CourseController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\CourseRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Course;
use App\Form\CourseType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class CourseController extends AbstractController
{
    #[Route('/courses', name: 'courses_index')]
    public function index(Request $request, CourseRepository $repository, ManagerRegistry $doctrine): Response
    {
        $courses = $repository->findAll();
        $colors = [];
        $entityManager = $doctrine->getManager();

        foreach ($courses as $course) {
            // If the course background is empty, generate a random color and persist it.
            if (empty($course->getBackground())) {
                $course->setBackground($this->generateRandomColor());
                $entityManager->persist($course);
            }
            $colors[] = $course->getBackground();
        }
        $entityManager->flush();

        return $this->render('courses/courses.html.twig', [
            'courses' => $courses,
            'colors'  => $colors,
        ]);
    }

    #[Route('/courses/{id}/{code}', name: 'courses_show', requirements: ['code' => '^(?!edit$).+'])]
    public function show(int $id, string $code, CourseRepository $repository): Response
    {
        $course = $repository->find($id);
        if (!$course) {
            throw $this->createNotFoundException('The requested course does not exist.');
        }
        if ($course->getCode() !== $code) {
            return $this->redirectToRoute('courses_show', [
                'code' => $course->getCode(),
                'id'   => $course->getId(),
            ], 301);
        }

        return $this->render('courses/course.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/search-courses', name: 'courses_search', methods: ['GET'])]
    public function searchCourses(Request $request, CourseRepository $courseRepository): JsonResponse
    {
        $term = $request->query->get('q', '');
        if (empty($term)) {
            return $this->json([]);
        }

        $courses = $courseRepository->searchCourses($term);
        $data = array_map(fn($course) => [
            'id'    => $course->getId(),
            'title' => $course->getTitle(),
            'code'  => $course->getCode(),
        ], $courses);

        return $this->json($data);
    }

    #[Route('/courses/new', name: 'courses_new')]
    #[IsGranted("ROLE_ADMIN")]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $course = new Course();
        $course->setCreatedAt(new \DateTimeImmutable());
        $course->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the uploaded image if provided.
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('course_pics_directory'),
                        $newFilename
                    );
                    $course->setBackground($newFilename);
                } catch (FileException $e) {
                    // Optionally log or handle the file upload exception.
                }
            }
            // Persist the course. If no image is uploaded, the background remains empty
            // and will be assigned a random color in the index() method.
            $entityManager = $doctrine->getManager();
            $entityManager->persist($course);
            $entityManager->flush();

            return $this->redirectToRoute('courses_index');
        }

        return $this->render('courses/new.html.twig', [
            'form'   => $form->createView(),
            'course' => $course,
        ]);
    }

    #[Route('/courses/edit/{id}', name: 'courses_edit')]
    #[IsGranted("ROLE_ADMIN")]
    public function edit(
        int $id,
        Request $request,
        CourseRepository $repository,
        ManagerRegistry $doctrine
    ): Response {
        $course = $repository->find($id);
        if (!$course) {
            throw $this->createNotFoundException('Course not found.');
        }

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the uploaded image if provided.
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('course_pics_directory'),
                        $newFilename
                    );
                    $course->setBackground($newFilename);
                } catch (FileException $e) {
                    // Optionally log or handle file upload exception.
                }
            }
            // Keep the existing background if no new image is uploaded.
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('courses_show', [
                'id'   => $course->getId(),
                'code' => $course->getCode()
            ]);
        }

        return $this->render('courses/edit.html.twig', [
            'form'   => $form->createView(),
            'course' => $course,
        ]);
    }

    /**
     * Generate a random hex color.
     *
     * @return string
     */
    private function generateRandomColor(): string
    {
        // The %06X ensures a 6-digit hexadecimal number with uppercase letters.
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}
