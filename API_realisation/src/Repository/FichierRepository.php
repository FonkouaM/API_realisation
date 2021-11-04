<?php

namespace App\Repository;

use App\Entity\Fichier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fichier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fichier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fichier[]    findAll()
 * @method Fichier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FichierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fichier::class);
    }

    // /**
    //  * @return Fichier[] Returns an array of Fichier objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Fichier
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

   /**
    * @Return files visible
    */
    public function selectFichiersVisible()
    {
       $query = $this->getEntityManager()->createQuery("SELECT f FROM App\Entity\Fichier f WHERE f.visible = 'actif' ");
            
       return $query->getResult();
    }
    
    /**
    * Return files visible_utilisateurid
    */
    public function FichiersUtilisateur($id)
    {
       $query = $this->getEntityManager()->createQuery("SELECT f FROM App\Entity\Fichier f WHERE f.visible = 'actif' AND f.utilisateur = $id ");
        // $query = $this->createQueryBuilder('f');
        // $query->from('Fichier', 'f')
        //       ->where('f.visible = actif')
        //       ->andWhere('f.utilisateur', $id);
       return $query->getResult();
    }

    /**
    * Return files visible_update
    */
    public function FichierUpdate($id)
    {
        $query = $this->getEntityManager()->createQuery("SELECT f FROM App\Entity\Fichier f WHERE f.visible = 'actif' AND f.id = $id ");
         
        return $query->getResult();
    }

}
