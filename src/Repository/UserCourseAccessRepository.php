<?php

namespace App\Repository;

use App\Entity\UserCourseAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Course; 

/**
 * @extends ServiceEntityRepository<UserCourseAccess>
 *
 * @method UserCourseAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCourseAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCourseAccess[]    findAll()
 * @method UserCourseAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCourseAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCourseAccess::class);
    }

    public function findLatestAccessByCourse(Course $course): array
    {
        return $this->createQueryBuilder('uca')
            ->select('IDENTITY(uca.user) AS user_id, MAX(uca.accessedAt) AS lastAccessed')
            ->andWhere('uca.course = :course')
            ->setParameter('course', $course)
            ->groupBy('uca.user')
            ->getQuery()
            ->getResult();
    }
}