<?php


namespace App\Service;


use App\Entity\Invoice;
use App\Entity\Payment;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Symfony\Component\HttpFoundation\Request;

class MollieService
{
    private $mollie;
    private $serviceId;

    public function __construct(Service $service)
    {
        $this->mollie = new MollieApiClient();
        $this->serviceId = $service->getId();
        try {
            $this->mollie->setApiKey($service->getAuthorization());
        }
        catch(ApiException $e){
            echo "<section><h2>Error: could not authenticate with Mollie API</h2><pre>". $e->getMessage()."</pre></section>";
        }
    }
    public function createPayment(Invoice $invoice, Request $request):string{
        //var_dump($request);
        $domain = $request->getHttpHost();
        if($request->isSecure())
            $protocol = "https://";
        else
            $protocol = "http://";
        $currency = $invoice->getPriceCurrency();
        $amount = $invoice->getPrice();
        $description = $invoice->getDescription();
        $redirectUrl = $invoice->getOrganization()->getRedirectUrl();
        $webhookUrl = "$protocol$domain/payments/molliewebhook?provider=$this->serviceId";
//        var_dump($webhookUrl);
//        die;
        try
        {
            $molliePayment = $this->mollie->payments->create([
                "amount" => [
                    "currency" => $currency,
                    "value" => $amount
                ],
                "description" => $description,
                "redirectUrl" => $redirectUrl,
                "webhookUrl" => $webhookUrl,
                "metadata" => [
                    "order_id" => $invoice->getReference(),
                ],
            ]);
            return $molliePayment->getCheckoutUrl();
        }
        catch (ApiException $e)
        {
            return "<section><h2>Could not connect to payment provider</h2>".$e->getMessage()."</section>";

        }
    }

    public function updatePayment(array $requestData, EntityManagerInterface $manager):Payment
    {
        $molliePayment = $this->mollie->payments->get($requestData['id']);
        $payment = $manager->getRepository('App:Payment')->findOneBy(['paymentId'=> $requestData['id']]);
        if($payment instanceof Payment) {
            $payment->setStatus($molliePayment->status);
            //return $payment;
        }
        else{
            $invoiceReference = $molliePayment->metadata['order_id'];
            $invoice = $manager->getRepository('App:Invoice')->findBy(['reference'=>$invoiceReference]);
            $payment = new Payment();
            $payment->setPaymentId($molliePayment->id);
            $payment->setStatus($molliePayment->status);
            $payment->setInvoice();
            $manager->persist($payment);
            $manager->flush();
        }
        return $payment;
    }
}
