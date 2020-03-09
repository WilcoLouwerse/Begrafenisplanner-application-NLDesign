<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use ApiPlatform\Core\Api\Entrypoint;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

use App\Entity\AuditTrail;
use App\Service\NLXLogService;

class AuditSubscriber implements EventSubscriberInterface
{
    private $params;
    private $em;
    private $serializer;
    private $nlxLogService;

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em, SerializerInterface $serializer, NLXLogService $nlxLogService)
    {
        $this->params = $params;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->nlxLogService = $nlxLogService;
    }

    public static function getSubscribedEvents()
    {
        return [
        		KernelEvents::VIEW => ['LogRequest', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function LogRequest(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();

        $session = new Session();
        //$session->start();
        // See: https://docs.nlx.io/further-reading/transaction-logs/

        $log = new AuditTrail();
        $log->setApplication($event->getRequest()->headers->get('X-NLX-Application-Id'));
        $log->setRequest($event->getRequest()->headers->get('X-NLX-Request-Id'));
        $log->setUser($event->getRequest()->headers->get('X-NLX-Request-User-Id'));
        $log->setSubject($event->getRequest()->headers->get('X-NLX-Request-Subject-Identifier'));
        $log->setProcess($event->getRequest()->headers->get('X-NLX-Request-Process-Id'));
        $log->setDataElements($event->getRequest()->headers->get('X-NLX-Request-Data-Elements'));
        $log->setDataSubjects($event->getRequest()->headers->get('X-NLX-Request-Data-Subject'));
        $log->setRoute($event->getRequest()->attributes->get('_route'));
        $log->setEndpoint($event->getRequest()->getPathInfo());
        $log->setMethod($event->getRequest()->getMethod());
        $log->setAccept($event->getRequest()->headers->get('Accept'));
        $log->setContentType($event->getRequest()->headers->get('Content-Type'));
        $log->setContent($event->getRequest()->getContent());
        $log->setIp($event->getRequest()->getClientIp());
        $log->setSession($session->getId());

        // 
        if(!$result instanceof Paginator && !$result instanceof Entrypoint) {
        	$log->setResource($result->getid());
        	$log->setResourceType($this->em->getMetadataFactory()->getMetadataFor(get_class($result))->getName());
        }
        
        $this->em->persist($log);
        $this->em->flush($log);

        // $authorization = $this->params->get('nlx.components.authorization.');
        // We need to do serveral things for  nlx

        // First of all we need to log this request  to our audit trial, where at minimal level we need to log who (application) asked what (data) for wich reasons (goal).  We also need to consider that the requestee might have used the field query parmeter. So we need to log what fields of the object where actually returned.

        // Then we need to authenticate the request against a common ground authentication component

        // In the current common ground we dont bother with authorization (every one may do anything as long as we know who it is)

        //return $result;
    }
    
    /**
     * @param EntityManager $em
     * @param string|object $class
     *
     * @return boolean
     */
    function isEntity(EntityManager $em, $class)
    {
    	if (is_object($class)) {
    		$class = ($class instanceof Proxy)
    		? get_parent_class($class)
    		: get_class($class);
    	}
    	
    	return ! $em->getMetadataFactory()->isTransient($class);
    }
}
