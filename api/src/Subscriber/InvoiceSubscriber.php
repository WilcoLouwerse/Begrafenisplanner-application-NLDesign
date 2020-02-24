<?php


namespace App\Subscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Organization;
use App\Entity\Payment;
use App\Entity\Tax;
use App\Service\MollieService;
use App\Service\SumUpService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use PhpParser\Error;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
            KernelEvents::REQUEST => ['invoice', EventPriorities::PRE_DESERIALIZE],
        ];
    }
    public function invoice(RequestEvent $event)
    {
//        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $route = $event->getRequest()->attributes->get('_route');

        //var_dump($route);
        $order =  json_decode($event->getRequest()->getContent(), true);

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

        if ($method != 'POST' && ($route != 'api_invoices_post_order_collection' || $order == null))
        {
            return;
        }
        $needed = array(
            '@id',
            'name',
            'description',
            'customer'
        );

        foreach($needed as $requirement){
            if(!key_exists($requirement, $order) || $order[$requirement] == null)
            {
                throw new BadRequestHttpException(sprintf('Compulsory property "%s" is not defined', $requirement));
            }
        }

        $invoice = new Invoice();
        $invoice->setName($order['name']);
        $invoice->setCustomer($order['customer']);
        $invoice->setOrder($order['@id']);
        $invoice->setDescription($order['description']);

        // invoice targetOrganization ip er vanuit gaan dat er een organisation object is meegeleverd
        $organization = $this->em->getRepository('App:Organization')->findOrCreateByRsin($order['targetOrganization']);

        if (!($organization instanceof Organization))
        {
        	// invoice targetOrganization ip er vanuit gaan dat er een organisation object is meegeleverd
            $organization = new Organization();
            $organization->setRsin($order['targetOrganization']);
            if(key_exists('organization', $order) && key_exists('shortCode',$order['organization']))
            {
            	$organization->setShortCode($order['organization']['shortCode']);
            }
        }

        $invoice->setOrganization($organization);
        $invoice->setTargetOrganization($order['targetOrganization']);

        if(key_exists('items',$order))
        {
        	foreach($order['items'] as $item){

                $invoiceItem = new InvoiceItem();
                $invoiceItem->setName($item['name']);
                $invoiceItem->setDescription($item['description']);
                $invoiceItem->setPrice($item['price']);
                $invoiceItem->setPriceCurrency($item['priceCurrency']);
                $invoiceItem->setOffer($item['offer']);
                $invoiceItem->setQuantity($item['quantity']);
                $invoice->addItem($invoiceItem);

                foreach($item['taxes'] as $taxPost){
                	$tax = new Tax();
                	$tax->setName($taxPost['name']);
                	$tax->setDescription($taxPost['description']);
                	$tax->setPrice($taxPost['price']);
                	$tax->setPriceCurrency($taxPost['priceCurrency']);
                	$tax->setPercentage($taxPost['percentage']);
                	$invoiceItem->addTax($tax);
                }
            }
        }

        // Lets throw it in the db
        $this->em->persist($invoice);
        $this->em->flush();

        // Only create payment links if a payment service is configured
        if(count($invoice->getOrganization()->getServices()) >0 )
        {
            //var_dump(count($invoice->getOrganization()->getServices()));
            $paymentService = $invoice->getOrganization()->getServices()[0];
            switch ($paymentService->getType()) {
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
        }

        $json = $this->serializer->serialize(
            $invoice,
            $renderType, ['enable_max_depth'=>true]
        );

		// Creating a response
        $response = new Response(
            $json,
            Response::HTTP_OK,
            ['content-type' => $contentType]
        );
        $event->setResponse($response);


        return $invoice;
    }
}
