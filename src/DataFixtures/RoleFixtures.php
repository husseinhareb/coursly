<?php
// src/DataFixtures/RoleFixtures.php
namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (['ROLE_ADMIN', 'ROLE_PROFESSOR', 'ROLE_STUDENT'] as $name) {
            $role = new Role();
            $role->setName($name);
            $manager->persist($role);
        }
        $manager->flush();
    }
}
