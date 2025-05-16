<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $roles = [
            ['ROLE_ADMIN', 'Administrateur avec tous les droits'],
            ['ROLE_MODERATOR', 'Modérateur avec droits limités'],
            ['ROLE_USER', 'Utilisateur classique'],

        ];
        foreach ($roles as [$name, $description]) {
            $role = new Role();
            $role->setName($name);
            $role->setDescription($description);
            $manager->persist($role);
        }
        $manager->flush();
    }
}
