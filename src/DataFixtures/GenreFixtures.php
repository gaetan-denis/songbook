<?php

namespace App\DataFixtures;

use App\Entity\Genre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GenreFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $genres = [
            ['Blues', 'Musique d’origine afro-américaine, souvent basée sur des grilles de 12 mesures.'],
            ['Jazz', 'Musique improvisée, influencée par le blues et la musique classique.'],
            ['Rock', 'Musique rythmée apparue dans les années 1950.'],
            ['Folk', 'Musique traditionnelle ou acoustique.'],
            ['Pop', 'Musique populaire aux mélodies accessibles.'],
            ['Classique', 'Musique savante occidentale.'],
            ['Reggae', 'Musique jamaïcaine à rythme ternaire.'],
            ['Metal', 'Genre basé sur des sons puissants et saturés.'],
            ['Funk', 'Style centré sur le groove et la rythmique.'],
            ['Rap', 'Musique avec du flow parlé/chanté sur un beat.'],
        ];

        foreach ($genres as [$name, $description]) {
            $genre = new Genre();
            $genre->setName($name);
            $genre->setDescription($description);
            $manager->persist($genre);
        }

        $manager->flush();
    }
}
