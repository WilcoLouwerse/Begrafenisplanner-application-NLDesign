<?php


namespace App\Subscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Invoice;
use App\Entity\Payment;
use App\Entity\Service;
use App\Service\MollieService;
use App\Service\SumUpService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use PhpParser\Error;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Yaml;

class PaymentSubscriber implements EventSubscriberInterface
{
    private $params;
    private $em;
    private $serializer;
    private $client;
    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em, SerializerInterface $serializer)
    {

        $this->params = $params;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->client = new Client();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['payment', EventPriorities::PRE_DESERIALIZE],
        ];
    }

    public function payment(RequestEvent $event)
    {
        //$result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $route = $event->getRequest()->attributes->get('_route');
        $contentType = $event->getRequest()->headers->get('accept');
        if (!$contentType) {
            $contentType = $event->getRequest()->headers->get('Accept');
        }
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
        //var_dump($route);
        if($route=='api_payments_post_webhook_collection'){
            //var_dump('a');
            $providerId = $event->getRequest()->query->get('provider');
           //var_dump($providerId);
            $provider = $this->em->getRepository('App\Entity\Service')->find($providerId);

            $paymentId = $event->getRequest()->request->get('id');
            //var_dump($paymentId);


            if($provider instanceof Service && $provider->getType() == 'mollie'){
                $mollieService = new MollieService($provider);
                $payment = $mollieService->updatePayment($paymentId, $provider, $this->em);
            }
            else{
                return;
            }
        }
        else{
            return;
        }


        if($payment){
            $this->em->persist($payment);
            $this->em->flush();
            $json = $this->serializer->serialize(
                $payment,
                $renderType, ['enable_max_depth'=>true]
            );
        }else{
            $json = $this->serializer->serialize(
                ["Error"=>"The payment is not related to an invoice in our database"], $renderType, ['enable_max_depth'=>true]
            );
        }

        // Creating a response
        $response = new Response(
            $json,
            Response::HTTP_CREATED,
            ['content-type' => $contentType]
        );
        $event->setResponse($response);
    }
}
