<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An entity representing an item of an invoice.
 *
 * This entity represents an item that is placed on the invoice
 *
 * @author Barry Brands <barry@conduction.nl>
 *
 * @category entity
 *
 * @license EUPL <https://github.com/ConductionNL/betaalservice/blob/master/LICENSE.md>
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceItemRepository")
 */
class InvoiceItem
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
     * @var string The name of the object
     *
     * @example My InvoiceItem
     * @Groups({"read","write"})
     * @Assert\Length(
     *     max=255
     * )
     * @Assert\NotNull
     * @ORM\Column(type="string", length=255)
     */
    private $name;
    /**
     * @var string The description of the InvoiceItem
     *
     * @example This is the best invoice item ever
     * @Groups({"read","write"})
     * @Assert\Length(
     *     max=255
     * )
     * @ORM\Column(type="string", length=2550, nullable=true)
     */
    private $description;

    /**
     * @var Invoice The invoice that contains this item
     *
     * @Groups({"read","write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice", inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    private $invoice;

    /**
     * @var string The offer this item represents
     *
     * @example http://example.org/offers/1
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     * @Assert\Url
     * @Assert\NotNull
     * @MaxDepth(1)
     */
    private $offer;

    /**
     * @var string The product this item represents. DEPRECATED: REPLACED BY OFFER
     *
     * @Groups({"read","write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     * @MaxDepth(1)
     * @Assert\Length(
     *     max = 255
     * )
     *
     * @deprecated
     */
    private $product;

    /**
     * @var int The quantity of the items that are ordered
     *
     * @example 1
     *
     * @Groups({"read","write"})
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\PositiveOrZero
     */
    private $quantity;

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
     * @var ArrayCollection The taxes that affect this offer
     *
     *
     * @MaxDepth(1)
     * @Groups({"read", "write"})
     * @ORM\OneToMany(targetEntity="App\Entity\Tax", mappedBy="invoiceItems")
     */
    private $taxes;

    /**
     * @var DateTime The moment this request was created by the submitter
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var DateTime The moment this request was created by the submitter
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateModified;

    public function __construct()
    {
    	$this->taxes = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @deprecated
     */
    public function getProduct(): ?string
    {
        if ($this->product) {
            return $this->product;
        } else {
            return $this->getOffer();
        }
    }

    /**
     * @deprecated
     */
    public function setProduct(string $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getOffer(): ?string
    {
        return $this->offer;
    }

    public function setOffer(string $offer): self
    {
        $this->offer = $offer;

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
     * @return Collection|Tax[]
     */
    public function getTaxes(): Collection
    {
    	return $this->taxes;
    }

    public function addTax(Tax $tax): self
    {
    	if (!$this->taxes->contains($tax)) {
    		$this->taxes[] = $tax;
    		$tax->addOffer($this);
    	}

    	return $this;
    }

    public function removeTax(Tax $tax): self
    {
    	if ($this->taxes->contains($tax)) {
    		$this->taxes->removeElement($tax);
    		$gtax->removeProduct($this);
    	}

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

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(?\DateTimeInterface $dateModified): self
    {
        $this->dateModified = $dateModified;

        return $this;
    }
}
