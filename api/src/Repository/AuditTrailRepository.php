<?php

namespace App\Repository;

use App\Entity\AuditTrail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AuditTrail|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuditTrail|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuditTrail[]    findAll()
 * @method AuditTrail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditTrailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditTrail::class);
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
