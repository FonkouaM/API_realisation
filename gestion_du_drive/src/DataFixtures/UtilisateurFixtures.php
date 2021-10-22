<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UtilisateurFixtures extends Fixture
{
    public const UTILISATEURS_REFERENCE = 'user';
    
    public function load(ObjectManager $manager)
    {  
        $faker = Factory::Create('en_US'); 
        $utilisateurs = Array();
       
        for ($i=0; $i<=30; $i++) {
            
            $utilisateurs[$i] = new Utilisateur();
            $utilisateurs[$i]->setNom($faker->lastName.$i);
            $utilisateurs[$i]->setPrenom($faker->firstName.$i);
            $utilisateurs[$i]->setEmail($faker->Email.$i);
            $utilisateurs[$i]->setTelephone($faker->seed(237));
            $utilisateurs[$i]->setPassword($faker->Password.$i);
        
            $manager->persist($utilisateurs[$i]);
            // $this->addReference(self::UTILISATEURS_REFERENCE.$i, $utilisateurs[$i]);
                    
            $this->addReference(self::UTILISATEURS_REFERENCE.$i, $utilisateurs[$i]);
        
        }
    
        $manager->flush();
        //$this->addReference(self::UTILISATEURS_REFERENCE, '$utilisateurs');

        //$this->addReference(self::PROJECT_REFERENCE.', $project);

    }
   
}
