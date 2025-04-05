<?php
// src/Controller/CourseController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CourseRepository;
use Doctrine\Persistence\ManagerRegistry; 
use App\Entity\Course;
use App\Form\CourseType;
final class CourseController extends AbstractController
{
    #[Route('/courses', name: 'courses.index')]
    public function index(Request $request, CourseRepository $repository): Response
    {
        $courses = $repository->findAll();

        // List of colors defined to vary the cards
        $colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#d35400', '#8e44ad'];

        return $this->render('courses/index.html.twig', [
            'courses' => $courses,
            'colors' => $colors,
        ]);
    }

    #[Route('/courses/{slug}-{id}', name: 'courses.show', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function show(string $slug, int $id, CourseRepository $repository): Response
    {  
        $course = $repository->find($id);
        
        if (!$course) {
            throw $this->createNotFoundException('The requested course does not exist.');
        }

        if ($course->getSlug() !== $slug) {
            return $this->redirectToRoute('courses.show', [
                'slug' => $course->getSlug(),
                'id' => $course->getId()
            ], 301);
        }
        
        return $this->render('courses/show.html.twig', [
            'course' => $course
        ]);
    }
    
    #[Route('/search-courses', name: 'search_courses', methods: ['GET'])]
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
            'slug'  => $course->getSlug(),
        ], $courses);

        return $this->json($data);
    }

    #[Route('/courses/new', name: 'courses.new')]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $course = new Course();
        $course->setCreatedAt(new \DateTimeImmutable());
        $course->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload if an image was provided
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
                    // Optionally log the error or notify the user
                }
            }

            $entityManager = $doctrine->getManager();
            $entityManager->persist($course);
            $entityManager->flush();

            return $this->redirectToRoute('courses.index');
        }

        return $this->render('courses/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
