<?php
// src/Repository/MessageCategoryRepository.php

namespace App\Repository;

use App\Entity\MessageCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageCategory::class);
    }

    // (optional) add your own finders here
}
