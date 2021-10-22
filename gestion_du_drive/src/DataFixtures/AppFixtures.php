<?php

namespace App\DataFixtures;

use App\DataFixtures\Faker;                                        
use App\Entity\Fichier;
use App\Entity\Utilisateur;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {  
//         $faker = Factory::Create('en_US'); 
//         $utilisateurs = Array();
//         $fichiers = Array();

//         for ($i=0; $i<=15; $i++) {
//             $utilisateurs[$i] = new Utilisateur();
//             $utilisateurs[$i]->setNom($faker->lastName);
//             $utilisateurs[$i]->setPrenom($faker->firstName);
//             $utilisateurs[$i]->setEmail($faker->Email);
//             $utilisateurs[$i]->setTelephone($faker->seed(237));
//             $utilisateurs[$i]->setPassword($faker->Password);
            
//             $manager->persist($utilisateurs[$i]);
//         }

//         for ($i = 0; $i <= 30; $i++) {
//             $fichiers[$i] = new Fichier();
//             $fichiers[$i]->setNom('fichier');
//             $fichiers[$i]->setDescription('description');
//             $fichiers[$i]->setLien('link_file');
//             $fichiers[$i]->setUtilisateur($utilisateurs[10]);

//             $manager->persist($fichiers[$i]);
//         }
        //$manager->flush();
        
    }

   
}