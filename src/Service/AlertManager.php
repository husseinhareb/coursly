<?php
// src/Service/AlertManager.php
namespace App\Service;

use App\Entity\{AdminAlert, Course, Post, User};
use Doctrine\ORM\EntityManagerInterface;

class AlertManager
{
    public function __construct(private EntityManagerInterface $em) {}

    public function raise(User $actor, string $action, Course $course, ?Post $post = null): void
    {
        // ne créer une alerte que si l’acteur N’EST PAS prof sur l’UE
        $isProf = $course->getUsers()->contains($actor) &&
                  in_array('ROLE_PROFESSOR', $actor->getRoles(), true);

        if ($isProf) {
            return;
        }

        $alert = new AdminAlert($course, $actor, $action, $post);
        $this->em->persist($alert);
        // on **ne flush pas ici** ⇒ laisser le contrôleur décider
    }
}
