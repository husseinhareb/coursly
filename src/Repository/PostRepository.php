<?php
// src/Repository/PostRepository.php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Retourner la valeur maximale de la position parmi tous les posts d'un cours donné
     * Si aucun post n'existe encore, retourner 0
     */
    public function findMaxPositionForCourse(Course $course): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('MAX(p.position) as maxPos')
            ->andWhere('p.course = :course')
            ->setParameter('course', $course);

        $max = $qb
            ->getQuery()
            ->getSingleScalarResult();

        // getSingleScalarResult() Retourne null s'il n'y a pas de lignes
        return (int) ($max ?? 0);
    }

     /**
     * @return Post[]
     */
    public function findRecentByUser(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.course', 'c')
            ->innerJoin('c.enrollments', 'e', 'WITH', 'e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

}
