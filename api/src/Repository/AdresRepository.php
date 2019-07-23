<?php

namespace App\Repository;

use App\Entity\Adres;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Adres|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adres|null findOneBy(array $criteria, array $orderBy = null)
 * @method Adres[]    findAll()
 * @method Adres[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdresRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Adres::class);
    }

    // /**
    //  * @return Adres[] Returns an array of Adres objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Adres
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
