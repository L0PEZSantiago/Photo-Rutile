<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Creation;
use App\Entity\Theme;
use App\Entity\Material;
use Symfony\Component\HttpFoundation\File\File;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $bois = $manager->getRepository(Material::class)->findOneBy(['name' => 'Bois']);
        $theme = $manager->getRepository(Theme::class)->findOneBy(['name' => 'Noël']);


            $creation = new Creation();
            $creation->setTitle('Creation 1');
            $creation->setDescription('Description of creation 1');
            $creation->setSlug('creation-1');
            $creation->setIsPublished(true);
            $creation->setTheme($theme);
            $creation->setMaterial($bois);
            $creation->setImageFile(new File('public/images/creations/default.webp'));
            $creation->setImageFilename('default.webp');
            $manager->persist($creation);

            $creation = new Creation();
            $creation->setTitle('Creation 2');
            $creation->setDescription('Description of creation 2');
            $creation->setSlug('creation-2');
            $creation->setIsPublished(true);
            $creation->setTheme($theme);
            $creation->setMaterial($bois);
            $creation->setImageFile(new File('public/images/creations/default.webp'));
            $creation->setImageFilename('default.webp');
            $manager->persist($creation);

            $creation = new Creation();
            $creation->setTitle('Creation 3');
            $creation->setDescription('Description of creation 3');
            $creation->setSlug('creation-3');
            $creation->setIsPublished(true);
            $creation->setTheme($theme);
            $creation->setMaterial($bois);
            $creation->setImageFile(new File('public/images/creations/default.webp'));
            $creation->setImageFilename('default.webp');
            $manager->persist($creation);

            $creation = new Creation();
            $creation->setTitle('Creation 4');
            $creation->setDescription('Description of creation 4');
            $creation->setSlug('creation-4');
            $creation->setIsPublished(true);
            $creation->setTheme($theme);
            $creation->setMaterial($bois);
            $creation->setImageFile(new File('public/images/creations/default.webp'));
            $creation->setImageFilename('default.webp');
            $manager->persist($creation);

            $creation = new Creation();
            $creation->setTitle('Creation 5');
            $creation->setDescription('Description of creation 5');
            $creation->setSlug('creation-5');
            $creation->setIsPublished(true);
            $creation->setTheme($theme);
            $creation->setMaterial($bois);
            $creation->setImageFile(new File('public/images/creations/default.webp'));
            $creation->setImageFilename('default.webp');
            $manager->persist($creation);
            $manager->flush();
 
    }
}