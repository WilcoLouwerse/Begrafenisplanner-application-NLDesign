<?php


namespace App\Subscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Invoice;
use App\Entity\Organization;
use App\Entity\Payment;
use App\Service\MollieService;
use App\Service\SumUpService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use PhpParser\Error;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class InvoiceSubscriber implements EventSubscriberInterface
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
            KernelEvents::VIEW => ['invoice', EventPriorities::PRE_VALIDATE],
        ];
    }
    public function invoice(ViewEvent $event)
    {
//        $result = $event->getControllerResult();
//        $method = $event->getRequest()->getMethod();
        $route = $event->getRequest()->attributes->get('_route');

//        var_dump($route);

        $data = json_decode($event->getRequest()->getContent());
        if ($route != 'api_invoices_post_order_collection' || $data == null)
        {
//            var_dump('a');
            return;
        }
        $order = $data;

        $invoice = new Invoice();
//        var_dump($order);
        $invoice->setName($order->name);
        $invoice->setDescription($order->description);
        $invoice->setReference($order->reference);
        $invoice->setPrice($order->price);
        $invoice->setPriceCurrency($order->priceCurrency);
        $invoice->setTax($order->tax);
        $invoice->setCustomer($order->customer);
        $invoice->setOrder($order->url);
        $organization = $this->em->getRepository('App:Organization')->findOrCreateByRsin($order->organization->rsin);
        if ($organization instanceof Organization)
        {
            if ($organization->getRsin() == $organization->getShortCode())
                $organization->setShortCode($order->organization->shortCode);
        }
        else
        {
            $organization = new Organization();
            $organization->setRsin($order->organization->rsin);
            $organization->setShortCode($order->organization->shortCode);
        }
        $invoice->setOrganization($organization);
        $invoice->setTargetOrganization($organization->getRsin());
        $this->em->persist($invoice);
        $this->em->persist($organization);
        $this->em->flush();
        $paymentService = $invoice->getOrganization()->getServices()[0];
        switch ($paymentService->getType()){
            case 'mollie':
                $mollieService = new MollieService($paymentService);
                $paymentUrl = $mollieService->createPayment($invoice, $event->getRequest());
                $invoice->setPaymentUrl($paymentUrl);
                break;
            case 'sumup':
                $sumupService = new SumUpService($paymentService);
                $paymentUrl = $sumupService->createPayment($invoice);
                $invoice->setPaymentUrl($paymentUrl);
        }
        $json = $this->serializer->serialize(
            $invoice,
            'jsonhal', ['enable_max_depth'=>true]
        );

        $response = new Response(
            $json,
            Response::HTTP_OK,
            ['content-type' => 'application/json+hal']
        );
        $event->setResponse($response);


//        return $invoice;
    }
}
