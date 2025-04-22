<?php
namespace App\Controller;

use App\Entity\AdminAlert;
use App\Entity\AlertAcknowledgement;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AlertController extends AbstractController
{
    #[Route('/alerts/{id}/ack', name: 'alerts_ack', methods:['POST'])]
    #[IsGranted('ROLE_PROFESSOR')]
    public function ack(AdminAlert $alert, ManagerRegistry $doctrine): JsonResponse
    {
        $user = $this->getUser();
        if ($alert->isAcknowledgedBy($user)) {
            return new JsonResponse(['ok'=>true]);
        }
        $ack = new AlertAcknowledgement($alert,$user);
        $em  = $doctrine->getManager();
        $em->persist($ack);
        $em->flush();
        return new JsonResponse(['ok'=>true]);
    }
}