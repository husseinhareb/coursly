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
        // 1) Créer l'entité Announcement (Annonce) et le formulaire associé
        $announcement = new Announcement();
        $form = $this->createForm(AnnouncementType::class, $announcement);
        $form->handleRequest($request);

        // 2) Traiter la soumission.
        if ($form->isSubmitted() && $form->isValid()) {
            // a) Définir l'auteur sur l'utilisateur actuellement connecté
            $announcement->setAuthor($this->getUser());

            // b) Gérer le téléchargement de fichier optionnel
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
                    // Afficher l'erreur et re-render le formulaire
                    $this->addFlash('error', 'announcement.upload_error');
                    return $this->render('announcement/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            // c) Persister et flush
            $em = $doctrine->getManager();
            $em->persist($announcement);
            $em->flush();

            $this->addFlash('success', 'announcement.created_success');
            // Rediriger vers la liste des annonces (vous devriez avoir une route d'index)
            return $this->redirectToRoute('announcement_index');
        }

        // 3) Rendre le formulaire de création
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
