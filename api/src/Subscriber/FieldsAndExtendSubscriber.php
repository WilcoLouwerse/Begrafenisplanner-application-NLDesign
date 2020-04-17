<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\SerializerInterface;

class FieldsAndExtendSubscriber implements EventSubscriberInterface
{
    private $params;
    private $serializer;
    private $propertyAccessor;

    public function __construct(ParameterBagInterface $params, SerializerInterface $serializer)
    {
        $this->params = $params;
        $this->serializer = $serializer;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['FilterFields', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function FilterFields(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $route = $event->getRequest()->attributes->get('_route');

        $fields = $event->getRequest()->query->get('fields');
        $extends = $event->getRequest()->query->get('extend');
        $contentType = $event->getRequest()->headers->get('accept');
        if (!$contentType) {
            $contentType = $event->getRequest()->headers->get('Accept');
        }

        // Only do somthing if fields is query supplied
        if ((!$fields && !$extends) || $method != 'GET') {
            return $result;
        }

        // Lets set a return content type
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

        // let turn fields into an array if it isn't one already
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        if (!is_array($extends)) {
            $extends = explode(',', $extends);
        }

        // Its possible to nest fields for filterins
        foreach ($fields as $key=>$value) {
            // Lets check if the fields contain one or more .'s
            if (strpos($value, '.') !== false) {
                // This is where it gets complicated couse it could go on indevinitly
            }
        }

        // Overwrite maxdepth for extended properties

        // we always need to return an id and links (in order not to break stuff)
        if (!in_array('id', $fields)) {
            $fields[] = 'id';
        }
        if (!in_array('@id', $fields)) {
            $fields[] = '@id';
        }
        if (!in_array('@type', $fields)) {
            $fields[] = '@type';
        }
        if (!in_array('@context', $fields)) {
            $fields[] = '@context';
        }

        // now we need to overide the normal subscriber
        $json = $this->serializer->serialize(
            $result,
            $renderType,
            ['enable_max_depth' => true,
                'attributes'    => $fields, ]
        );

        /*
        $jsonArray = json_decode($json, true);
        // The we want to extend properties from the extend query
        foreach($extends as $extend){
        	// @todo add security checks
        	// Get new object for the extend
        	$extendObject = $this->propertyAccessor->getValue($result, $extend);
        	// turn to json
        	$extendjson = $this->serializer->serialize(
        		$result,
        		$type,
        		['enable_max_depth' => true,
        		'attributes'=> $fields]
        	);

        	// add to the array
        	$jsonArray[$extend] = json_decode($extendjson, true);
        }

        $response = $this->serializer->serialize(
            $jsonArray,
            $renderType, ['enable_max_depth'=>true]
        );
        */

        // Creating a response
        $response = new Response(
            $json,
            Response::HTTP_CREATED,
            ['content-type' => $contentType]
        );
        $event->setResponse($response);
    }
}
