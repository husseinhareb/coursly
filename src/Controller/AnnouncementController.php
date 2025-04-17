<?php
// src/Controller/AnnouncementController.php
namespace App\Controller;

use App\Entity\Announcement;
use App\Form\AnnouncementType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AnnouncementController extends AbstractController
{
    #[Route('/announcements/new', name: 'announcement_new')]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        // 1) Create the Announcement entity and associate form
        $announcement = new Announcement();
        $form = $this->createForm(AnnouncementType::class, $announcement);
        $form->handleRequest($request);

        // 2) Process submission
        if ($form->isSubmitted() && $form->isValid()) {
            // a) Set the author to the currently logged‐in user
            $announcement->setAuthor($this->getUser());

            // b) Handle optional file upload
            $uploaded = $form->get('file')->getData();
            if ($uploaded) {
                $newFilename = uniqid('annc_') . '.' . $uploaded->guessExtension();
                try {
                    $uploaded->move(
                        $this->getParameter('announcement_files_directory'),
                        $newFilename
                    );
                    $announcement->setFilePath($newFilename);
                } catch (FileException $e) {
                    // flash error and re-render form
                    $this->addFlash('error', 'announcement.upload_error');
                    return $this->render('announcement/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            // c) Persist and flush
            $em = $doctrine->getManager();
            $em->persist($announcement);
            $em->flush();

            $this->addFlash('success', 'announcement.created_success');
            // Redirect to the list of announcements (you should have an index route)
            return $this->redirectToRoute('announcement_index');
        }

        // 3) Render the creation form
        return $this->render('announcement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


        #[Route('/announcements', name: 'announcement_index')]
        public function index(ManagerRegistry $doctrine): Response
        {
            $announcements = $doctrine->getRepository(Announcement::class)
                                    ->findBy([], ['createdAt' => 'DESC']);

            return $this->render('announcement/announcement.html.twig', [
                'announcements' => $announcements,
            ]);
        }

}
