<?php

namespace App\Subscriber;

use ApiPlatform\Core\Api\Entrypoint;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\AuditTrail;
use App\Service\NLXLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

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
        $request = $event->getRequest();
        $responce = $event->getResponse();
        $session = new Session();

        //$session->start();
        // See: https://docs.nlx.io/further-reading/transaction-logs/

        $log = new AuditTrail();
        $log->setApplication($request->headers->get('X-NLX-Application-Id'));
        $log->setRequest($request->headers->get('X-NLX-Request-Id'));
        $log->setUser($request->headers->get('X-NLX-Request-User-Id'));
        $log->setSubject($request->headers->get('X-NLX-Request-Subject-Identifier'));
        $log->setProcess($request->headers->get('X-NLX-Request-Process-Id'));
        $log->setRoute($request->attributes->get('_route'));
        $log->setEndpoint($request->getPathInfo());
        $log->setMethod($request->getMethod());
        $log->setAccept($request->headers->get('Accept'));
        $log->setContentType($request->headers->get('Content-Type'));
        $log->setContent($request->getContent());
        $log->setIp($request->getClientIp());
        $log->setSession($session->getId());
        $log->setHeaders($request->headers->all());

        if ($event->getRequest()->headers->get('X-NLX-Request-Data-Elements')) {
            $log->setDataElements(explode(',', $event->getRequest()->headers->get('X-NLX-Request-Data-Elements')));
        }
        if ($event->getRequest()->headers->get('X-NLX-Request-Data-Subject')) {
            $log->setDataSubjects(explode(',', $event->getRequest()->headers->get('X-NLX-Request-Data-Subject')));
        }

        //
        if ($result != null && !$result instanceof Paginator && !$result instanceof Entrypoint && !is_array($result)) {

            $log->setResource($result->getid());
            $log->setResourceType($this->em->getMetadataFactory()->getMetadataFor(get_class($result))->getName());
        }

        // Responce loging
        //$log->setStatusCode($responce->getStatusCode());
        //$log->setNotFound($responce->isNotFound());
        //$log->setForbidden($responce->isForbidden());
        //$log->setOk($responce->isOk());

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
     * @return bool
     */
    public function isEntity(EntityManager $em, $class)
    {
        if (is_object($class)) {
            $class = ($class instanceof Proxy)
            ? get_parent_class($class)
            : get_class($class);
        }

        return !$em->getMetadataFactory()->isTransient($class);
    }
}
