<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ServiceRepository")
 */
class Service
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
     *     "mollie",
     *     "sumup"
     *     }
     * )
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @var Organization The organization this payment provider is linked to
     *
     * @Groups({"read","write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", inversedBy="services")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organization;

    /**
     * @var string The API key for this payment provider
     *
     * @Groups({"write"})
     * @ORM\Column(type="string", length=255, name="auth")
     */
    private $authorization;

    /**
     * @var array Configuration options for this payment provider
     * @Groups({"read","write"})
     *
     * @ORM\Column(type="array", nullable=true)
     */
    private $configuration = [];

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

    public function getAuthorization(): ?string
    {
        return $this->authorization;
    }

    public function setAuthorization(string $authorization): self
    {
        $this->authorization = $authorization;

        return $this;
    }

    public function getConfiguration(): ?array
    {
        return $this->configuration;
    }

    public function setConfiguration(?array $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }
}
