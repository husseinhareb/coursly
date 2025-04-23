<?php
// src/Repository/CourseRepository.php
namespace App\Repository;

use App\Entity\Course;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * @param int $duration
     * @return Course[]
     */
    /*public function findWithDurationLowerThan(int $duration): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.duration <= :duration')
            ->orderBy('c.duration', 'ASC')
            ->setMaxResults()
            ->setParameter('duration', $duration)
            ->getQuery()
            ->getResult();
    }
    */

    // /**
    //  * @return Course[] Returns an array of Course objects
    //  */
    // public function findByExampleField($value): array
    // {
    //     return $this->createQueryBuilder('c')
    //         ->andWhere('c.exampleField = :val')
    //         ->setParameter('val', $value)
    //         ->orderBy('c.id', 'ASC')
    //         ->setMaxResults(10)
    //         ->getQuery()
    //         ->getResult();
    // }
    //
    // public function findOneBySomeField($value): ?Course
    // {
    //     return $this->createQueryBuilder('c')
    //         ->andWhere('c.exampleField = :val')
    //         ->setParameter('val', $value)
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }
    
    public function searchCourses(string $term): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.title LIKE :term OR c.code LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->getQuery()
            ->getResult();
    }
    public function findCoursesForUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.enrollments', 'e')
            ->where('e.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.enrollments', 'e', 'WITH', 'e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
