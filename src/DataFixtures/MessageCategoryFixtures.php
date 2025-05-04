<?php
// src/DataFixtures/MessageCategoryFixtures.php

namespace App\DataFixtures;

use App\Entity\MessageCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MessageCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Les deux minimums de la spécification, plus quelques extras :
        $names = [
            'Information',
            'Important',
            'Reminder',
            'Announcement',
            'Tip',
        ];

        foreach ($names as $name) {
            $cat = new MessageCategory($name);
            $manager->persist($cat);
        }

        $manager->flush();
    }
}
