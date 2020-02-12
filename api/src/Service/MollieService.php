<?php


namespace App\Service;


use App\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\MollieApiClient;
use Symfony\Component\HttpFoundation\Request;

class MollieService
{
    private $mollie;

    public function __construct()
    {
        $mollie = new MollieApiClient();
        $mollie->setApiKey('');
    }
    public function createPayment(Request $request):Payment{

        $payment = new Payment();

        $currency = $request["currency"];
        $amount = $request["amount"];
        $description = $request["description"];
        //@TODO: make return url configurable
        $redirectUrl = "https://www.conduction.nl/betaling/".(string)$payment->getId();

        $payment->setInvoice($request['invoice']);
        $payment->setCurrency($currency);
        $payment->setAmount($amount);
        $payment->setDescription($description);
        $payment->setPaymentProvider($request["paymentProvider"]);

        $molliePayment = $this->mollie->payments->create([
           "amount"=>[
               "currency"=>$currency,
               "value"=>$amount
           ],
            "description"=>$description,
            "redirectUrl"=>$redirectUrl,
            "webhookUrl"=>"https://bs.conduction.nl/payments/molliewebhook"
        ]);


        $payment->setPaymentId($molliePayment->id);
        $payment->setStatus($molliePayment->status);
        $payment->setPaymentUrl($molliePayment->getCheckoutUrl);

        return $payment;
    }

    public function updatePayment(Request $request, EntityManagerInterface $manager):Payment
    {
        $molliePayment = $this->mollie->payments->get($request['id']);
        $payment = $manager->getRepository('App:Payment')->findOneBy(['paymentId'=> $request['id']]);
        if($payment instanceof Payment) {
            $payment->setStatus($molliePayment->status);
            return $payment;
        }
        return null;
    }
}
