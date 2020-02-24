<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Money\Currency;
use Money\Money;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An entity representing an invoice.
 *
 * This entity represents an invoice for sales
 *
 * @author Barry Brands <barry@conduction.nl>
 *
 * @category entity
 *
 * @license EUPL <https://github.com/ConductionNL/betaalservice/blob/master/LICENSE.md>
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true},
 *     collectionOperations={
 *          "get",
 *          "post",
 *          "post_order"={
 *              "method"="POST",
 *              "path"="invoices/order",
 *              "swagger_context" = {
 *                  "summary"="Create an invoice by just providing an order",
 *                  "description"="Create an invoice by just providing an order"
 *              }
 *          }
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 * @ORM\Table(name="invoices")
 * @ORM\HasLifecycleCallbacks
 */
class Invoice
{
    /**
     * @var UuidInterface The UUID identifier of this object
     *
     * @example e2984465-190a-4562-829e-a8cca81aa35d
     *
     *
     * @Groups({"read"})
     * @Assert\Uuid
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var string The name of the invoice
     *
     * @example My Invoice
     * @Groups({"read","write"})
     * @Assert\Length(
     *     max=255
     * )
     * @Assert\NotNull
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string The description of the invoice
     *
     * @example This is the best invoice ever
     * @Groups({"read","write"})
     * @Assert\Length(
     *     max=255
     * )
     * @ORM\Column(type="string", length=2550, nullable=true)
     */
    private $description;

    /**
     * @var string The human readable reference for this request, build as {gemeentecode}-{year}-{referenceId}. Where gemeentecode is a four digit number for gemeenten and a four letter abriviation for other organizations
     *
     * @example 6666-2019-0000000012
     *
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true) //, unique=true
     * @ApiFilter(SearchFilter::class, strategy="exact")
     * @Assert\Length(
     *     max = 255
     * )
     */
    private $reference;

    /**
     * @var string The autoincrementing id part of the reference, unique on a organization-year-id basis
     *
     * @ORM\Column(type="integer", length=11, nullable=true)
     * @Assert\Length(
     *     max = 11
     * )
     */
    private $referenceId;

    /**
     * @var string The RSIN of the organization that ownes this proces
     *
     * @example 002851234
     *
     * @Assert\NotNull
     * @Assert\Length(
     *     max = 255
     * )
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     * @ApiFilter(SearchFilter::class, strategy="exact")
     */
    private $targetOrganization;

    /**
     * @var ArrayCollection The items in this invoice
     *
     * @Groups({"read", "write"})
     * @ORM\OneToMany(targetEntity="App\Entity\InvoiceItem", mappedBy="invoice", cascade={"persist"})
     * @MaxDepth(1)
     */
    private $items;

    /**
     * @var string The price of this product
     *
     * @example 50.00
     *
     * @Groups({"read","write"})
     * @Assert\NotNull
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    private $price;

    /**
     * @var string The currency of this product in an [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217) format
     *
     * @example EUR
     *
     * @Assert\Currency
     * @Groups({"read","write"})
     * @ORM\Column(type="string")
     */
    private $priceCurrency;
    
    /**
     * @var array A list of total taxes
     *
     * @example EUR
     *
     * @Groups({"read"})
     * @ORM\Column(type="array")
     */
    private $taxes = [];

    /**
     * @var DateTime The moment this request was created by the submitter
     *
     * @example 20190101
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var DateTime The moment this request was created by the submitter
     *
     * @example 20190101
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateModified;

    /**
     * @var string The order of this invoice
     *
     * @example https://www.example.org/order/1
     *
     * @Assert\Length(
     *     max = 255
     * )
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, name="order_uri")
     */
    private $order;

    /**
     * @var Payment The payments of this Invoice
     *
     * @Groups({"read", "write"})
     * @ORM\OneToMany(targetEntity="App\Entity\Payment", mappedBy="invoice")
     * @MaxDepth(1)
     */
    private $payments;

    /**
     * @var string The customer that receives this invoice
     * @example https://example.org/people/1
     *
     * @Groups({"read","write"})
     * @Assert\Url
     * @Assert\NotNull
     * @ORM\Column(type="string", length=255)
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organization;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paymentUrl;

    /**
     * @var string Remarks on this invoice
     *
     * @Groups({"read","write"})
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $remark;
    
    /**
     *
     *  @ORM\PrePersist
     *  @ORM\PreUpdate
     *
     *  */
    public function prePersist()
    {
    	/*@todo we should support non euro */
    	$price = new Money(0, new Currency('EUR'));
    	$taxes = [];
    	
    	foreach ($this->items as $item){
    		
    		// Calculate Invoice Price
    		//
    		if(is_string ($item->getPrice())){
    			//Value is a string, so presumably a float
    			$float = floatval($item->getPrice());
    			$float = $float*100;
    			$itemPrice = new Money((int) $float, new Currency($item->getPriceCurrency()));
    			
    		}
    		else{
    			// Calculate Invoice Price
    			$itemPrice = new Money($item->getPrice(), new Currency($item->getPriceCurrency()));
    			
    			
    		}
    		
    		$itemPrice = $itemPrice->multiply($item->getQuantity());
    		$price = $price->add($itemPrice);
    		
    		// Calculate Taxes
    		/*@todo we should index index on something else do, there might be diferend taxes on the same percantage. Als not all taxes are a percentage */
    		foreach($item->getTaxes() as $tax){
    			if(!array_key_exists($tax->getPercentage(), $taxes)){
    				$tax[$tax->getPercentage()] = $itemPrice->multiply($tax->getPercentage()/100);
    			}
    			else{
    				$taxPrice = $itemPrice->multiply($tax->getPercentage()/100);
    				$tax[$tax->getPercentage()] = $tax[$tax->getPercentage()]->add($taxPrice);
    			}
    		}
    		
    	}
    	
    	$this->taxes = $taxes;
    	$this->price = $price->getAmount()/100;
    	$this->priceCurrency = $price->getCurrency();
    }
    
    
    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getReferenceId(): ?int
    {
        return $this->reference;
    }

    public function setReferenceId(int $referenceId): self
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    public function getTargetOrganization(): ?string
    {
        return $this->targetOrganization;
    }

    public function setTargetOrganization(string $targetOrganization): self
    {
        $this->targetOrganization = $targetOrganization;

        return $this;
    }

    /**
     * @return Collection|InvoiceItem[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(InvoiceItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setInvoice($this);
        }

        return $this;
    }

    public function removeItem(InvoiceItem $item): self
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            // set the owning side to null (unless already changed)
            if ($item->getInvoice() === $this) {
                $item->setInvoice(null);
            }
        }

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDateCreated(): ?DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
    public function getDateModified(): ?DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(DateTimeInterface $dateModified): self
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    public function getPriceCurrency(): ?string
    {
        return $this->priceCurrency;
    }

    public function setPriceCurrency(string $priceCurrency): self
    {
        $this->priceCurrency = $priceCurrency;

        return $this;
    }
    
    /**
     * @return Array
     */
    public function getTaxes(): Array
    {
    	return $this->taxes;
    }

    public function getOrder(): ?string
    {
        return $this->order;
    }

    public function setOrder(?string $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return Collection|Payment[]
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setInvoice($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->contains($payment)) {
            $this->payments->removeElement($payment);
            // set the owning side to null (unless already changed)
            if ($payment->getInvoice() === $this) {
                $payment->setInvoice(null);
            }
        }

        return $this;
    }

    public function getCustomer(): ?string
    {
        return $this->customer;
    }

    public function setCustomer(string $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getPaymentUrl(): ?string
    {
        return $this->paymentUrl;
    }

    public function setPaymentUrl(?string $paymentUrl): self
    {
        $this->paymentUrl = $paymentUrl;

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }


}
