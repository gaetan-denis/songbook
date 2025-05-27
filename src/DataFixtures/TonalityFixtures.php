<?php

namespace App\DataFixtures;

use App\Entity\Tonality;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TonalityFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tonalities = [
            // Majeures naturelles
            ['C',  'Do majeur', 'major'],
            ['D',  'Ré majeur', 'major'],
            ['E',  'Mi majeur', 'major'],
            ['F',  'Fa majeur', 'major'],
            ['G',  'Sol majeur', 'major'],
            ['A',  'La majeur', 'major'],
            ['B',  'Si majeur', 'major'],

            // Mineures naturelles
            ['Cm',  'Do mineur', 'minor'],
            ['Dm',  'Ré mineur', 'minor'],
            ['Em',  'Mi mineur', 'minor'],
            ['Fm',  'Fa mineur', 'minor'],
            ['Gm',  'Sol mineur', 'minor'],
            ['Am',  'La mineur', 'minor'],
            ['Bm',  'Si mineur', 'minor'],

            // Dièses majeures
            ['C#',  'Do dièse majeur', 'major'],
            ['D#',  'Ré dièse majeur', 'major'],
            ['F#',  'Fa dièse majeur', 'major'],
            ['G#',  'Sol dièse majeur', 'major'],
            ['A#',  'La dièse majeur', 'major'],

            // Dièses mineures
            ['C#m', 'Do dièse mineur', 'minor'],
            ['D#m', 'Ré dièse mineur', 'minor'],
            ['F#m', 'Fa dièse mineur', 'minor'],
            ['G#m', 'Sol dièse mineur', 'minor'],
            ['A#m', 'La dièse mineur', 'minor'],

            // Bémols majeures
            ['Db',  'Ré bémol majeur', 'major'],
            ['Eb',  'Mi bémol majeur', 'major'],
            ['Gb',  'Sol bémol majeur', 'major'],
            ['Ab',  'La bémol majeur', 'major'],
            ['Bb',  'Si bémol majeur', 'major'],

            // Bémols mineures
            ['Dbm', 'Ré bémol mineur', 'minor'],
            ['Ebm', 'Mi bémol mineur', 'minor'],
            ['Gbm', 'Sol bémol mineur', 'minor'],
            ['Abm', 'La bémol mineur', 'minor'],
            ['Bbm', 'Si bémol mineur', 'minor'],
        ];

        foreach ($tonalities as [$name, $description, $type]) {
            $tonality = new Tonality();
            $tonality->setName($name);
            $tonality->setDescription($description);
            $tonality->setType($type);
            $manager->persist($tonality);
        }

        $manager->flush();
    }
}
