<?php


namespace App\Subscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Invoice;
use App\Entity\Organization;
use App\Entity\Payment;
use App\Service\MollieService;
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
use Symfony\Component\Yaml\Yaml;

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
            KernelEvents::VIEW => ['invoice', EventPriorities::PRE_SERIALIZE],
        ];
    }
    public function invoice(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $route = $event->getRequest()->attributes->get('_route');
        $data = json_decode($this->event->getRequest()->getContent());
        if (!$result instanceof Invoice || $route != 'api_invoice_post_order_collection' || $data['order'] != null)
        {
            //var_dump('a');
            return;
        }
        $order = $data["order"];

        $invoice = new Invoice();

        $invoice->setName($order['name']);
        $invoice->setDescription($order['description']);
        $invoice->setReference($order['reference']);
        $invoice->setPrice($order['price']);
        $invoice->setPriceCurrency($order['priceCurrency']);
        $invoice->setTax($order['tax']);
        $invoice->setOrder($order);
        $invoice->setCustomer($order['customer']);

        $this->em->persist($invoice);

        $organization = $this->em->getRepository('App\Entity\Organization')->findOrCreateByRsin($order['organization']['shortCode']);
        if ($organization instanceof Organization)
        {
            if ($organization->getRsin() == $organization->getShortCode())
                $organization->setShortCode($order['organization']['shortCode']);
        }
        else
        {
            $organization = new Organization();
            $organization->setRsin($order['organization']['rsin']);
            $organization->setShortCode($order['organization']['shortCode']);
        }
        $this->em->persist($organization);
        $invoice->setOrganization($organization);
        $this->em->persist($invoice);
    }
}
