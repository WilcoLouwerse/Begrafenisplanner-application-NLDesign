<?php

namespace App\Repository;

use App\Entity\NLXRequestLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method NLXRequestLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method NLXRequestLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method NLXRequestLog[]    findAll()
 * @method NLXRequestLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NLXRequestLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExampleEntity::class);
    }

    /**
     * @return NLXRequestLog[] Returns an array of NLXRequestLog objects
     */
    public function getLogEntries($entity)
    {
        return $this->createQueryBuilder('l')
        ->where('l.objectClass = :objectClass')
        ->setParameter('objectClass', $this->getEntityManager()->getMetadataFactory()->getMetadataFor(get_class($entity))->getName())
        ->andWhere('l.objectId = :objectId')
        ->setParameter('objectId', $entity->getId())
        ->orderBy('l.loggedAt', 'DESC')
        ->getQuery()
        ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?NLXRequestLog
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
