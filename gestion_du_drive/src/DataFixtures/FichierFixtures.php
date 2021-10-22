<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use App\Entity\Fichier;
use App\DataFixtures\UtilisateurFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class FichierFixtures extends Fixture implements DependentFixtureInterface
{
    // public const UTILISATEURS_REFERENCE = 'utilisateur';
    public function load(ObjectManager $manager)
    {  
        $faker = Factory::Create('en_US'); 
        $fichiers = Array();
        // $utilisateurs = [$i];

        for ($i = 0; $i <30; $i++) {
            // if ($i <= 14){ 
                $fichiers[$i] = new Fichier();
                $fichiers[$i]->setNom('fichier');
                $fichiers[$i]->setDescription('description');
                $fichiers[$i]->setLien('link_file');
                // $fichiers[$i]->setUtilisateur($utilisateurs[3]);           
                $fichiers[$i]->setUtilisateur($this->getReference(UtilisateurFixtures::UTILISATEURS_REFERENCE.$i));
            
                $manager->persist($fichiers[$i]);
            // }else
            // {        
            //     $fichiers[$i]->setUtilisateur($this->getReference(UtilisateurFixtures::UTILISATEURS_REFERENCE.$i+1));
            
            //     $manager->persist($fichiers[$i]);
            // }
        }
        $manager->flush();
    }
    
    public function getDependencies()
    {
        return [
            UtilisateurFixtures::class,
        ];
    }
    
}