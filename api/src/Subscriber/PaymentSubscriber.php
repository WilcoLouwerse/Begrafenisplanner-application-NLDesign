<?php


namespace App\Subscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Payment;
use App\Service\MollieService;
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
            KernelEvents::VIEW => ['payment', EventPriorities::PRE_SERIALIZE],
        ];
    }
    public function payment(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $route = $event->getRequest()->attributes->get('_route');
        $mollieService = new MollieService();
//        var_dump($route);
        if(!$result instanceof Payment || ($route != 'api_payment_post_webhook_collection' || $route != 'api_payment_post_collection')){
            //var_dump('a');
            return;
        }
        elseif($route=='api_payment_post_webhook_collection'){
            $payment = $mollieService->updatePayment($event->getRequest(), $this->em);
        }else{
            $payment = $mollieService->createPayment($event->getRequest());
        }
        $this->em->persist($payment);
        $this->em->flush();


        $json = json_encode($payment);

        $response = new Response(
            $json,
            Response::HTTP_OK,
            ['content-type' => 'application/json+hal']
        );
        $event->setResponse($response);



    }
}
