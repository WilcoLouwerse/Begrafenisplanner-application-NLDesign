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
            KernelEvents::VIEW => ['payment', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function payment(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $route = $event->getRequest()->attributes->get('_route');

        //var_dump($route);
        if($result instanceof Payment && $route=='api_payments_post_webhook_collection'){
            $providerId = $event->getRequest()->query->get('provider');
            $provider = $this->em->getRepository('App\Entity\Service')->find($providerId);

            $requestData = json_decode($event->getRequest()->getContent(),true);




            if($provider instanceof Service && $provider->getType() == 'mollie'){
                $mollieService = new MollieService($provider);
                $payment = $mollieService->updatePayment($requestData, $this->em);
            }
            else{
                return;
            }
        }
        else{
            return;
        }
        $this->em->persist($payment);
        $this->em->flush();


        $json = $this->serializer->serialize(
            $payment,
            'jsonhal', ['enable_max_depth'=>true]
        );

        $response = new Response(
            $json,
            Response::HTTP_OK,
            ['content-type' => 'application/json+hal']
        );
        $event->setResponse($response);
    }
}
