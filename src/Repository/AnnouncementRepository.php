<?php

namespace App\Repository;

use App\Entity\Announcement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Announcement>
 *
 * @method Announcement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Announcement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Announcement[]    findAll()
 * @method Announcement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnouncementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Announcement::class);
    }

    // You can add your own custom query methods here, e.g.:
    //
    // /**
    //  * @return Announcement[]
    //  */
    // public function findLatest(int $limit = 5): array
    // {
    //     return $this->createQueryBuilder('a')
    //         ->orderBy('a.createdAt', 'DESC')
    //         ->setMaxResults($limit)
    //         ->getQuery()
    //         ->getResult();
    // }
}
