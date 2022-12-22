<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{    public function __construct( private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
         $User1= new User();
         $User1->setNom("Zbidi");
         $User1->setPrenom("Arsalene");
         $User1->setEmail("arsalene.zbidi@isticbc.org");
         $User1->setMobile("90431500");
         $User1->setCin("09636981");
         $User1->setAdresse("19,rue ibn zaidoun,boumhal");
         $User1->setPassword($this->hasher->hashPassword($User1,"arsalene123"));
         $User1->setUsername("Arsalene-zbidi");

        $manager->persist($User1);

        $manager->flush();
    }
}
