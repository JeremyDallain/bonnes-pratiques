<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    public function load(ObjectManager $manager)
    {
        for ($u=1; $u <= 10; $u++) { 
            
            $user = new User();
            $user->setEmail("user$u@gmail.com")
                ->setPassword($this->passwordEncoder->encodePassword($user, "1234"));
                        
            $manager->persist($user);
    
        }
        $manager->flush();
    }
}
