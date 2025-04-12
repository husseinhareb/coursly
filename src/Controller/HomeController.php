<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;  
use App\Entity\UserCourseAccess;        

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $recentCourses = [];
        
        if ($user) {
            $entityManager = $doctrine->getManager();
            $accessRepository = $entityManager->getRepository(UserCourseAccess::class);
            $recentAccesses = $accessRepository->findBy(
                ['user' => $user],
                ['accessedAt' => 'DESC'],
                5 // Limit to 5 recent courses.
            );
            
            // Extract courses from the access records.
            foreach ($recentAccesses as $access) {
                $recentCourses[] = $access->getCourse();
            }
        }
        
        return $this->render('/home/home.html.twig', [
            'recentCourses' => $recentCourses,
        ]);
    }
}
