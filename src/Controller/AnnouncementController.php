<?php
// src/Controller/AnnouncementController.php
namespace App\Controller;

use App\Entity\Announcement;
use App\Form\AnnouncementType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnnouncementController extends AbstractController
{
    #[Route('/announcements/new', name: 'announcement_new')]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        // Create a new announcement entity
        $announcement = new Announcement();

        // Build the form using the standalone AnnouncementType
        $form = $this->createForm(AnnouncementType::class, $announcement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the author to the currently logged-in user
            $user = $this->getUser();
            if ($user) {
                $announcement->setAuthor($user);
            }

            // Handle file upload if provided
            $uploadedFile = $form->get('file')->getData();
            if ($uploadedFile) {
                $newFilename = uniqid('annc_') . '.' . $uploadedFile->guessExtension();
                try {
                    $uploadedFile->move(
                        $this->getParameter('announcement_files_directory'),
                        $newFilename
                    );
                    $announcement->setFilePath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('warning', 'Could not upload file.');
                }
            }

            // Persist the announcement
            $em = $doctrine->getManager();
            $em->persist($announcement);
            $em->flush();

            $this->addFlash('success', 'Announcement created successfully.');
            return $this->redirectToRoute('announcement_new');
        }

        // Render the form template
        return $this->render('announcement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
