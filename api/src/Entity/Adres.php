<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\AdresRepository")
 */
class Addres
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $oppervlakte;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="integer", length=255, nullable=true)
     */
    private $huisnummer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $huisnummerToevoeging;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $straat;

    /**
     * @ORM\Column(type="string", length=8, nullable=true)
     */
    private $postcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $woonplaats;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $links = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOppervlakte(): ?int
    {
        return $this->oppervlakte;
    }

    public function setOppervlakte(?int $oppervlakte): self
    {
        $this->oppervlakte = $oppervlakte;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getHuisnummer(): ?int
    {
        return $this->huisnummer;
    }

    public function setHuisnummer(?int $huisnummer): self
    {
        $this->huisnummer = $huisnummer;

        return $this;
    }

    public function getHuisnummerToevoeging(): ?string
    {
        return $this->huisnummerToevoeging;
    }

    public function setHuisnummerToevoeging(?string $huisnummerToevoeging): self
    {
        $this->huisnummerToevoeging = $huisnummerToevoeging;

        return $this;
    }

    public function getStraat(): ?string
    {
        return $this->straat;
    }

    public function setStraat(?string $straat): self
    {
        $this->straat = $straat;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getWoonplaats(): ?string
    {
        return $this->woonplaats;
    }

    public function setWoonplaats(?string $woonplaats): self
    {
        $this->woonplaats = $woonplaats;

        return $this;
    }

    public function getLinks(): ?array
    {
        return $this->links;
    }

    public function setLinks(?array $_links): self
    {
        $this->links = $_links;

        return $this;
    }
}
