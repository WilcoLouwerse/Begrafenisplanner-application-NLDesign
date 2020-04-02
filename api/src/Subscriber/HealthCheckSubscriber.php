<?php

namespace App\Subscriber;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\AuditTrail;
use App\Service\NLXLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class HealthCheckSubscriber implements EventSubscriberInterface
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
            KernelEvents::VIEW => ['Audittrail', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function Audittrail(GetResponseForControllerResultEvent $event)
    {
        $method = $event->getRequest()->getMethod();
        $contentType = $event->getRequest()->headers->get('accept');
        if (!$contentType) {
            $contentType = $event->getRequest()->headers->get('Accept');
        }

        // Only do somthing if we are on te log route and the entity is logable
        if ($method != 'GET' || !strpos($contentType, 'health') || !strpos($contentType, 'json')) {
            return;
        }

        // Lets get the rest of the data

        $route = $event->getRequest()->attributes->get('_route');
        $result = $event->getControllerResult();

        switch ($contentType) {
            case 'application/json':
                $renderType = 'json';
                break;
            case 'application/ld+json':
                $renderType = 'jsonld';
                break;
            case 'application/hal+json':
                $renderType = 'jsonhal';
                break;
            default:
                $contentType = 'application/json';
                $renderType = 'json';
        }

        $results = [
            'status'    => 'pass',
            'version'   => '1',
            'releaseID' => $this->params->get('app_version'),
            'notes'     => [],
            'output'    => '',
        ];

        if (!$result instanceof Paginator) {
            $result['serviceID'] = $result->getid();
            $result['description'] = $this->em->getMetadataFactory()->getMetadataFor(get_class($result))->getName();
        }

        /*


        $results = $this->em->getRepository('App:AuditTrail')->findBy(['resource'=> $itemId,'resourceType'=> $entityType]);

        */
        $response = $this->serializer->serialize(
            $results,
            'json',
            ['enable_max_depth'=> true]
        );

        // Creating a response

        $response = new Response(
            $response,
            Response::HTTP_CREATED,
            ['content-type' => $contentType]
        );

        $event->setResponse($response);
    }
}
