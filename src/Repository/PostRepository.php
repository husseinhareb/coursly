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
     * Return the maximum position value among all Posts of a given Course.
     * If there are no Posts yet, returns 0.
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

        // getSingleScalarResult() returns null if there are no rows
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
