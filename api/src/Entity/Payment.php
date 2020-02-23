<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An entity representing a payment.
 *
 * This entity represents a payment of an invoice.
 *
 * @author Barry Brands <barry@conduction.nl>
 * @license EUPL <https://github.com/ConductionNL/betaalservice/blob/master/LICENSE.md>
 *
 * @category entity
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true},
 *     collectionOperations={
 *          "get",
 *          "post",
 *          "post_webhook"={
 *              "method"="POST",
 *              "path"="payments/mollie_webhook",
 *              "input_formats"={"x-www-form-urlencoded"={"application/x-www-form-urlencoded"}},
 *              "swagger_context" = {
 *                  "summary"="Webhook to update payment statuses from Mollie",
 *                  "description"="Webhook to update payment statuses from Mollie"
 *              }
 *          }
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment
{
    /**
     * @var UuidInterface
     *
     * @example e2984465-190a-4562-829e-a8cca81aa35d
     *
     * @Assert\Uuid
     * @Groups({"read"})
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var Service The provider that handles the payment
     *
     * @Assert\NotNull
     * @Groups({"read", "write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Service")
     * @ORM\JoinColumn(nullable=false)
     */
    private $paymentProvider;

    /**
     * @var string The payment id of this payment
     *
     * @example 87782426a21cbd70fc9823cbe1e024fb25804c833743b41529a23ae94b3b1cc2
     *
     * @Assert\NotNull
     * @Assert\Length(
     *     max = 255
     * )
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     */
    private $paymentId;

    /**
     * @var string The status of this payment
     *
     * @example open
     *
     * @Assert\NotNull
     * @Assert\Length(
     *     max = 255
     * )
     * @Assert\Choice(
     *     {
     *     "open",
     *     "pending",
     *     "authorized",
     *     "expired",
     *     "failed",
     *     "paid"
     *     }
     * )
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @var Invoice The invoice this payment relates to
     *
     * @Groups({"read", "write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice", inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     * @MaxDepth(1)
     */
    private $invoice;

    public function getId()
    {
        return $this->id;
    }

    public function getPaymentProvider(): ?Service
    {
        return $this->paymentProvider;
    }

    public function setPaymentProvider(Service $paymentProvider): self
    {
        $this->paymentProvider = $paymentProvider;

        return $this;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(string $paymentId): self
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }
}
