<?php
namespace App\Repository;

use App\Entity\AdminAlert;
use App\Entity\Course;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<AdminAlert> */
class AdminAlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminAlert::class);
    }

    /**
     * Retourne les alertes NON acquittées pour $user sur $course.
     *
     * @return AdminAlert[]
     */
    public function findUnreadByCourseAndUser(Course $course, User $user): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.acknowledgements', 'ack', 'WITH', 'ack.user = :user')
            ->andWhere('a.course = :course')
            ->andWhere('ack.id IS NULL')
            ->setParameter('course', $course)
            ->setParameter('user',   $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Toutes les alertes non lues de $user (dashboard).
     *
     * @return AdminAlert[]
     */
    public function findUnreadByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.acknowledgements', 'ack', 'WITH', 'ack.user = :user')
            ->innerJoin('a.course',         'c')
            ->innerJoin('c.enrollments',    'e', 'WITH', 'e.user = :user')
            ->andWhere('ack.id IS NULL')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
