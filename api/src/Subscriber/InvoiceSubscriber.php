<?php


namespace App\Subscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Invoice;
use App\Entity\Organization;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class InvoiceSubscriber implements EventSubscriber
{
    private $params;
    private $em;
    private $serializer;
    private $nlxLogService;

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->params = $params;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->index($args);
    }

    public function index(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if(!($entity  instanceof Invoice))
        {
            //var_dump('a');
            return $entity;
        }
        //var_dump('b');
        if(!$entity->getReference()){
            $organisation = $entity->getOrganization();

            if(!$organisation || !($organisation instanceof Organization)){
                $organisation = $this->em->getRepository('App\Entity\Organization')->findOrCreateByRsin($entity->getTargetOrganization());
                $this->em->persist($organisation);
                $this->em->flush();
                $entity->addOrganization($organisation);
            }

            $referenceId = $this->em->getRepository('App\Entity\Invoice')->getNextReferenceId($organisation);
            $entity->setReferenceId($referenceId);
            $entity->setReference($organisation->getShortCode().'-'.date('Y').'-'.$referenceId);
        }

        return $entity;
    }
}
