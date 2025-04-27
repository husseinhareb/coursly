<?php
// src/Controller/HomeController.php

namespace App\Controller;

use App\Entity\Announcement;
use App\Entity\UserCourseAccess;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(Request $request, ManagerRegistry $doctrine): Response
    {
        // if no user is logged in, redirect to your login route
        if (null === $this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user           = $this->getUser();
        $recentCourses  = [];
        $announcements  = [];
        $em             = $doctrine->getManager();

        // 1) Recent courses
        $accessRepo      = $em->getRepository(UserCourseAccess::class);
        $recentAccesses  = $accessRepo->findBy(
            ['user' => $user],
            ['accessedAt' => 'DESC'],
            5
        );
        foreach ($recentAccesses as $access) {
            $recentCourses[] = $access->getCourse();
        }

        // 2) Latest announcements
        $annRepo        = $em->getRepository(Announcement::class);
        $announcements  = $annRepo->findBy(
            [],                    
            ['createdAt' => 'DESC'],
            5                       
        );

        return $this->render('home/home.html.twig', [
            'recentCourses' => $recentCourses,
            'announcements' => $announcements,
        ]);
    }
}
