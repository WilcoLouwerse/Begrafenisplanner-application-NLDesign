<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\NLXRequestLog;
use App\Service\NLXLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class NLXSubscriber implements EventSubscriberInterface
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
                KernelEvents::VIEW => ['NLXLog', EventPriorities::PRE_VALIDATE],
                KernelEvents::VIEW => ['NLXAudit', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function NLXAudit(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();
        $auditTrail = $event->getRequest()->query->get('auditTrail');

        // Only do somthing if we are on te log route and the entity is logable
        /* @todo we should trhow errors here foruser feedback */
        if (!$auditTrail) {
            return $result;
        }

        $repo = $this->em->getRepository('App\Entity\NLXRequestLog');
        $logs = $repo->getLogEntries($result);

        // now we need to overide the normal subscriber
        $json = $this->serializer->serialize(
                $logs,
                'jsonhal', ['enable_max_depth' => true]
                );

        $response = new Response(
                $json,
                Response::HTTP_OK,
                ['content-type' => 'application/json+hal']
                );

        $event->setResponse($response);
    }

    public function NLXLog(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();

        $session = new Session();
        $session->start();
        // See: https://docs.nlx.io/further-reading/transaction-logs/

        $log = new NLXRequestLog();
        $log->setApplicationId($event->getRequest()->headers->get('X-NLX-Application-Id'));
        $log->setRequestId($event->getRequest()->headers->get('X-NLX-Request-Id'));
        $log->setUserId($event->getRequest()->headers->get('X-NLX-Request-User-Id'));
        $log->setSubjectId($event->getRequest()->headers->get('X-NLX-Request-Subject-Identifier'));
        $log->setProcessId($event->getRequest()->headers->get('X-NLX-Request-Process-Id'));
        $log->setDataElements($event->getRequest()->headers->get('X-NLX-Request-Data-Elements'));
        $log->setDataSubjects($event->getRequest()->headers->get('X-NLX-Request-Data-Subject'));
        $log->setObjectId($result->getid());
        $log->setObjectClass($this->em->getMetadataFactory()->getMetadataFor(get_class($result))->getName());
        $log->setRoute($event->getRequest()->attributes->get('_route'));
        $log->setEndpoint($event->getRequest()->getPathInfo());
        $log->setMethod($event->getRequest()->getMethod());
        $log->setContentType($event->getRequest()->headers->get('Content-Type'));
        $log->setContent($event->getRequest()->getContent());
        $log->setSession($session->getId());
        $log->setLoggedAt(new \DateTime());

        $this->em->persist($log);
        $this->em->flush($log);

        // $authorization = $this->params->get('nlx.components.authorization.');
        // We need to do serveral things for  nlx

        // First of all we need to log this request  to our audit trial, where at minimal level we need to log who (application) asked what (data) for wich reasons (goal).  We also need to consider that the requestee might have used the field query parmeter. So we need to log what fields of the object where actually returned.

        // Then we need to authenticate the request against a common ground authentication component

        // In the current common ground we dont bother with authorization (every one may do anything as long as we know who it is)

        return $result;
    }
}
