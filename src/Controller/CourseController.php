<?php
// src/Controller/CourseController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CourseRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Course;
use App\Form\CourseType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class CourseController extends AbstractController
{
    #[Route('/courses', name: 'courses_index')]
    public function index(Request $request, CourseRepository $repository): Response
    {
        $courses = $repository->findAll();
        $colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#d35400', '#8e44ad'];

        return $this->render('courses/courses.html.twig', [
            'courses' => $courses,
            'colors' => $colors,
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
                'id' => $course->getId()
            ], 301);
        }
        
        return $this->render('courses/course.html.twig', [
            'course' => $course
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
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $course = new Course();
        $course->setCreatedAt(new \DateTimeImmutable());
        $course->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('courses_images_directory'),
                        $newFilename
                    );
                    $course->setImagePath($newFilename);
                } catch (FileException $e) {
                }
            }

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

    #[Route('/courses/{id}/edit', name: 'courses_edit')]
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
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('courses_images_directory'),
                        $newFilename
                    );
                    $course->setImagePath($newFilename);
                } catch (FileException $e) {
                }
            }
            
            $entityManager = $doctrine->getManager();
            $entityManager->flush();
            
            return $this->redirectToRoute('courses_show', [
                'id' => $course->getId(),
                'code' => $course->getCode()
            ]);
        }
        
        return $this->render('courses/edit.html.twig', [
            'form' => $form->createView(),
            'course' => $course,
        ]);
    }
}
