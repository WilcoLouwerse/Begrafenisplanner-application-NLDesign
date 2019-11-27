<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class FieldsSubscriber implements EventSubscriberInterface
{
    private $params;
    private $serializer;

    public function __construct(ParameterBagInterface $params, SerializerInterface $serializer)
    {
        $this->params = $params;
        $this->serializer = $serializer;
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
        $fields = $event->getRequest()->query->get('fields');

        // Only do somthing if fields is query supplied
        if (!$fields) {
            return $result;
        }

        // let turn fields into an array if it isn't one already
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }

        // we always need to return an id and links (in order not to break stuff)
        if (!in_array('id', $fields)) {
            $fields[] = 'id';
        }
        if (!in_array('_links', $fields)) {
            $fields[] = '_links';
        }

        // now we need to overide the normal subscriber
        $json = $this->serializer->serialize(
            $result,
            'jsonhal', ['enable_max_depth' => true, 'attributes'=> $fields]
        );

        $response = new Response(
                $json,
                Response::HTTP_OK,
                ['content-type' => 'application/json+hal']
                );

        $event->setResponse($response);
    }
}
