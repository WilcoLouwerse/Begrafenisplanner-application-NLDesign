<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An resource representing a log line.
 *
 * This entity represents a product that can be ordered via the OrderRegistratieComponent.
 *
 * @author Ruben van der Linde <ruben@conduction.nl>
 *
 * @category Entity
 *
 * @license EUPL <https://github.com/ConductionNL/productenendienstencatalogus/blob/master/LICENSE.md>
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true}
 * )
 * @ApiFilter(OrderFilter::class, properties={
 * 		"action",
 * 		"objectId",
 * 		"objectClass",
 * 		"version",
 * 		"username",
 * 		"dateCreated",
 * 		"dateModified",
 * })
 * @ApiFilter(SearchFilter::class, properties={
 * 		"action": "exact",
 * 		"objectId": "exact",
 * 		"objectClass": "exact",
 * 		"version": "exact",
 * })
 * @ApiFilter(DateFilter::class, properties={"dateCreated","dateModified" })
 * @ORM\Entity(repositoryClass="App\Repository\ChangeLogRepository")
 */
class ChangeLog extends AbstractLogEntry
{
    /**
     * @var UuidInterface The UUID identifier of this object
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
    protected $id;

    /**
     * @var string A note conserning this log lin
     *
     * @example This log line is suspicius
     *
     * @Assert\Length(
     *      max = 2555
     * )
     * @Groups({"read","write"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @var string
     *
     * @Groups({"read"})
     * @ORM\Column(type="string", length=8)
     */
    protected $action;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="logged_at", type="datetime")
     */
    protected $loggedAt;

    /**
     * @var string
     *
     * @Groups({"read"})
     * @ORM\Column(name="object_id", length=64, nullable=true)
     */
    protected $objectId;

    /**
     * @var string
     *
     * @Groups({"read"})
     * @ORM\Column(name="object_class", type="string", length=255)
     */
    protected $objectClass;

    /**
     * @var int
     *
     * @Groups({"read"})
     * @ORM\Column(type="integer")
     */
    protected $version;

    /**
     * @var array
     *
     * @Groups({"read"})
     * @ORM\Column(type="array", nullable=true)
     */
    protected $data;

    /**
     * @var string
     *
     * @Groups({"read"})
     * @ORM\Column(length=255, nullable=true)
     */
    protected $username;

    /**
     * @var sting The moment this request was created
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $session;

    /**
     * @var Datetime The moment this request was created
     *
     * @Assert\DateTime
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var Datetime The moment this request last Modified
     *
     * @Assert\DateTime
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateModified;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSession(): ?string
    {
        return $this->session;
    }

    public function setSession(string $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(\DateTimeInterface $dateModified): self
    {
        $this->dateModified = $dateModified;

        return $this;
    }
}
