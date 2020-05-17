<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class ValidOnSubscriber implements EventSubscriberInterface
{
    private $params;
    private $em;
    private $serializer;
    private $annotationReader;

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em, SerializerInterface $serializer, Reader $annotationReader)
    {
        $this->params = $params;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->annotationReader = $annotationReader;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['validOn', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function validOn(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $route = $event->getRequest()->attributes->get('_route');

        // Lets get validOn from the query but deafult back to geldig op (for backward compatibality with api standaard)
        $geldigOp = $event->getRequest()->query->get('geldigOp', false);
        $validOn = $event->getRequest()->query->get('validOn', $geldigOp);

        // Only do somthing if fields is query supplied
        if (!$validOn || $method != 'GET') {
            return $result;
        }

        // Lets see if this class has a Loggableannotation
        $loggable = false;
        $reflClass = new \ReflectionClass($result);
        $annotations = $this->annotationReader->getClassAnnotations($reflClass);

        foreach ($annotations as $annotation) {
            if (get_class($annotation) == "Gedmo\Mapping\Annotation\Loggable") {
                $loggable = true;
            }
        }
        /* @todo propper error handling */
        if (!$loggable) {
            throw new \Exception('This enity is not loggable therefore no previus versions can be obtained');
        }

        // Lets turn valid on into a date
        try {
            $date = strtotime($validOn);
            $date = date('Y-m-d H:i:s', $date);
        } catch (Exception $e) {
            /* @todo thow propper exeption */
            throw new \Exception('Caught exception: ', $e->getMessage(), "\n");
        }

        // Lets try to get an version valid on that date
        $queryBuilder = $this->em->getRepository('App\Entity\ChangeLog')->createQueryBuilder('l')
            ->where('l.objectClass = :objectClass')
            ->setParameter('objectClass', $this->em->getMetadataFactory()->getMetadataFor(get_class($result))->getName())
            ->andWhere('l.objectId = :objectId')
            ->setParameter('objectId', $result->getId())
            ->andWhere('l.loggedAt <= :loggedAt')
            ->setParameter('loggedAt', $date)
            ->setMaxResults(1)
            ->orderBy('l.loggedAt', 'DESC');

        $version = $queryBuilder->getQuery()->getOneOrNullResult();

        /* @todo propper error handling */
        if (!$version) {
            throw new \Exception('Could not find a valid version for date: '.$date);
        }

        // Lets use the found version to rewind the object and return is
        $repo = $this->em->getRepository('App\Entity\ChangeLog'); // we use default log entry class
        $repo->revert($result, $version->getVersion());

        return $result;
    }
}
