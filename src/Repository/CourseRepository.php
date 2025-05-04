<?php
// src/Repository/CourseRepository.php
namespace App\Repository;

use App\Entity\Course;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

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
     * Search courses by title or code using raw SQL.
     *
     * @param string $term
     * @return Course[]
     */
    public function searchCourses(string $term): array
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();

        // Build a ResultSetMapping to hydrate Course entities
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(Course::class, 'c');

        $sql = '
            SELECT c.*
            FROM course c
            WHERE c.title LIKE :term
               OR c.code  LIKE :term
        ';

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter('term', '%' . $term . '%');

        return $query->getResult();
    }

    /**
     * Find all courses a given user is enrolled in, via raw SQL.
     *
     * @param User $user
     * @return Course[]
     */
    public function findCoursesForUser(User $user): array
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(Course::class, 'c');

        $sql = '
            SELECT c.*
            FROM course c
            INNER JOIN enrollment e
                ON c.id = e.course_id
            WHERE e.user_id = :userId
            ORDER BY c.title ASC
        ';

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    /**
     * Find all courses for a user, ordered by title, using raw SQL.
     *
     * @param User $user
     * @return Course[]
     */
    public function findByUser(User $user): array
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(Course::class, 'c');

        $sql = '
            SELECT c.*
            FROM course c
            INNER JOIN enrollment e
                ON c.id = e.course_id
            WHERE e.user_id = :userId
            ORDER BY c.title ASC
        ';

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    /**
     * Find courses with duration lower than or equal to given value, raw SQL.
     *
     * @param int $duration
     * @param int|null $limit
     * @return Course[]
     */
    public function findWithDurationLowerThan(int $duration, ?int $limit = null): array
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(Course::class, 'c');

        $sql = '
            SELECT c.*
            FROM course c
            WHERE c.duration <= :duration
            ORDER BY c.duration ASC
        ';
        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
        }

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter('duration', $duration);
        if ($limit !== null) {
            $query->setParameter('limit', $limit);
        }

        return $query->getResult();
    }
}
