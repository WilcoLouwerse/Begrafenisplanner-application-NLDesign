<?php

namespace App\Repository;

use App\Entity\ChangeLog;
use Doctrine\Common\Persistence\ManagerRegistry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;

/**
 * @method ChangeLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChangeLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChangeLog[]    findAll()
 * @method ChangeLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChangeLogRepository extends LogEntryRepository
{
    /*
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChangeLog::class);
    }
    */

    // /**
    //  * @return ChangeLog[] Returns an array of ChangeLog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ChangeLog
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
