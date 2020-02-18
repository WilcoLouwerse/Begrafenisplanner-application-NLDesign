<?php

// src/entity/ExampleEntity.php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Filter\LikeFilter;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This is an example entity.
 *
 * With an adtional description, all in all its pritty nice [url](www.google.nl)
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true},
 *     itemOperations={
 *     		"get","put","delete",
 *     		"audittrail"={
 *     			"method"="GET",
 *     			"name"="Provides an auditrail for this entity",
 *     			"description"="Provides an auditrail for this entity"
 *     		}
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ExampleEntityRepository")
 * @Gedmo\Loggable
 * @ApiFilter(LikeFilter::class, properties={"name","description"})
 */
class ExampleEntity
{
    /**
     * @var \Ramsey\Uuid\UuidInterface The id of this entity
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
     * @var string The name of this example property
     *
     * @example My Group
     *
     * @Assert\NotNull
     * @Assert\Length(
     *      max = 255
     * )
     * @Gedmo\Versioned
     * @Groups({"read","write"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string The description of this example property
     *
     * @example Is the best group ever
     *
     * @Assert\Length(
     *      max = 2555
     * )
     * @Gedmo\Versioned
     * @Groups({"read","write"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string Proof that we camel case our api
     *
     * @example Best api ever
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Gedmo\Versioned
     * @Groups({"read","write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $camelCase;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
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

    public function getCamelCase(): ?string
    {
        return $this->camelCase;
    }

    public function setCamelCase(?string $camelCase): self
    {
        $this->camelCase = $camelCase;

        return $this;
    }
}
