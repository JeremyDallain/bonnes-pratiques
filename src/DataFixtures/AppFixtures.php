<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Article;
use Bezhanov\Faker\Provider\Commerce;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    protected $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager)
    {        

        $faker = Factory::create('fr_FR');
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));

        for ($a=0; $a < 20; $a++) { 

            $article = new Article();
            $article->setTitle($faker->department())
                ->setContent($faker->paragraph())
                ->setCreatedAt($faker->dateTimeBetween('-1 month', 'now'))
                ->setSlug(strtolower($this->slugger->slug($article->getTitle())));

            $manager->persist($article);
        }
        

        $manager->flush();
    }
}
