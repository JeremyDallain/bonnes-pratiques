<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Article;
use Bezhanov\Faker\Provider\Commerce;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    protected $slugger;

    public function __construct(SluggerInterface $slugger, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->slugger = $slugger;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {        
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));

        $admin = new User();
        $hash = $this->passwordEncoder->encodePassword($admin, '1234');
        $admin->setEmail("admin@gmail.com")
            ->setPassword($hash)
            ->setRoles(['ROLE_ADMIN'])
            ->setFullName('John Doe');               
        $manager->persist($admin);

        for ($u=1; $u <= 10; $u++) { 
            
            $user = new User();
            $user->setEmail("user$u@gmail.com")
                ->setFullName($faker->name())
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->passwordEncoder->encodePassword($user, "1234"));
            
            $manager->persist($user);
                        
            for ($a=0; $a < mt_rand(3, 8); $a++) { 
                
                $article = new Article();
                $article->setTitle($faker->department())
                    ->setContent($faker->paragraph())
                    ->setCreatedAt($faker->dateTimeBetween('-1 month', 'now'))
                    ->setSlug(strtolower($this->slugger->slug($article->getTitle())))
                    ->setUser($user);
                
                $manager->persist($article);
            } 
        }
        $manager->flush();                
    }
}
