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
        // Order liever naar aray forcen dan object (arrays kunnnen mninder dus zijn veiliger ens choner )
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

        // Je wilt op method = POST een check
        if ($method != 'POST' && ($route != 'api_invoices_post_order_collection' || $order == null))
        {
            return;
        }

        // Gloabaal willen we hier checken of aantal dingen voorkomen of een fout gooien
        // @id description name customer

        $invoice = new Invoice();
        $invoice->setName($order['name']);
        $invoice->setCustomer($order['customer']);
        $invoice->setOrder($order['@id']);
        $invoice->setDescription($order['description']);

        // invoice targetOrganization ip er vanuit gaan dat er een organisation object is meegeleverd
        $organization = $this->em->getRepository('App:Organization')->findOrCreateByRsin($order['targetOrganization']);

        if ($organization instanceof Organization)
        {
        	// bij if graag {} gebruiken voor leesbaarheid, en wat doet dit?
            //if ($organization->getRsin() == $organization->getShortCode()){
           // 	$organization->setShortCode($order['organization']['shortCode']);
            //}
        }
        else
        {
        	// invoice targetOrganization ip er vanuit gaan dat er een organisation object is meegeleverd
            $organization = new Organization();
            $organization->setRsin($order['targetOrganization']);
            if($order['organization'] && key_exists('shortCode',$order['organization'])){ // moet array keycheck worden
            	$organization->setShortCode($order['organization']['shortCode']);
            }
        }

        $invoice->setOrganization($organization);
        $invoice->setTargetOrganization($order['targetOrganization']);

        // Waarom hier persisten ?
        //$this->em->persist($invoice);

        if(isset($order['items']))// moet array keycheck worden
        {
        	foreach($order['items'] as $item){

                $invoiceItem = new InvoiceItem();
                $invoiceItem->setName($item['name']);
                $invoiceItem->setDescription($item['description']);
                $invoiceItem->setPrice($item['price']);
                $invoiceItem->setPriceCurrency($item['priceCurrency']);
                $invoiceItem->setOffer($item['offer']);
                $invoiceItem->setQuantity($item['quantity']);
                //$this->em->persist($invoiceItem); // cascade
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

        // Wat als er geen payment providers zijn
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

        // Hier is ergens een keer een beter switch voor gebouwd
        $json = $this->serializer->serialize(
            $invoice,
            $renderType, ['enable_max_depth'=>true]
        );

		// Creating a responce
        $response = new Response(
            $json,
            Response::HTTP_OK,
            ['content-type' => $contentType]
        );
        $event->setResponse($response);


        return $invoice;
    }
}
