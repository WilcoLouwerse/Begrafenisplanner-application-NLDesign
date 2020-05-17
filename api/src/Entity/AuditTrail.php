<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
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
 * 		"application","request",
 * 		"user",
 * 		"subject",
 * 		"processs",
 * 		"resource",
 * 		"resourceType",
 * 		"endpoint",
 * 		"contentType",
 * 		"content",
 * 		"session",
 * 		"dateCreated",
 * 		"dateModified",
 * })
 * @ApiFilter(SearchFilter::class, properties={
 * 		"applicationId": "exact",
 * 		"request": "exact",
 * 		"user": "exact",
 * 		"subject": "exact",
 * 		"processs": "exact",
 * 		"resource": "exact",
 * 		"resourceType": "partial",
 * 		"endpoint": "exact",
 * 		"contentType": "exact",
 * 		"content": "exact",
 * 		"session": "exact",
 * })
 * @ApiFilter(DateFilter::class, properties={"dateCreated","dateModified" })
 * @ORM\Entity(repositoryClass="App\Repository\AuditTrailRepository")
 */
class AuditTrail
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
    private $id;

    /**
     * @var string A note conserning this log lin
     *
     * @example This log line looks suspicius
     *
     * @Assert\Length(
     *      max = 2555
     * )
     * @Groups({"read","write"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @var sting The application that made the request
     *
     * @Assert\Url
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $application;

    /**
     * @var sting The id of the request within that application
     *
     * @Assert\Url
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $request;

    /**
     * @var sting The user on behalf of wich the request was made
     *
     * @Assert\Url
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true, name="username")
     */
    private $user;

    /**
     * @var sting ???
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @var sting The procces on behalf of wich the request was made
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $process;

    /**
     * @var array The moment this request was created
     *
     * @Groups({"read"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $dataElements = [];

    /**
     * @var array The moment this request was created
     *
     * @Groups({"read"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $dataSubjects = [];

    /**
     * @var sting The resource that was requested
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resource;

    /**
     * @var sting The type of the resource that was requested
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resourceType;

    /**
     * @var sting The moment this request was created
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $route;

    /**
     * @var sting The endpoint that the request was made to
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $endpoint;

    /**
     * @var sting The method that was used
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=10)
     */
    private $method;

    /**
     * @var sting The contentType that was reqousted
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $accept;

    /**
     * @var sting The contentType that was suplieds
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $contentType;

    /**
     * @var sting The moment this request was created
     *
     * @Assert\Length(
     *      max = 2555
     * )
     * @Groups({"read"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @var sting The moment this request was created
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $ip;

    /**
     * @var string The moment this request was created
     *
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $session;

    /**
     * @var array The headers supplied by client
     *
     * @Groups({"read"})
     * @ORM\Column(type="array")
     */
    private $headers = [];

    /**
     * @var int The status code returned to client
     *
     * @example 200
     *
     * @Groups({"read"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $statusCode;

    /**
     * @var bool Whether or not the reqousted endpoint was found
     *
     * @example false
     *
     * @Groups({"read"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $notFound = false;

    /**
     * @var bool Whether or not the client was allowed to the reqousted endpoint
     *
     * @example false
     *
     * @Groups({"read"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $forbidden = false;

    /**
     * @var bool Whether or not there where any problems
     *
     * @example true
     *
     * @Groups({"read"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $ok = true;

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

    public function setApplication(?string $application): self
    {
        $this->application = $application;

        return $this;
    }

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function setRequest(?string $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(?string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getProcess(): ?string
    {
        return $this->process;
    }

    public function setProcess(?string $process): self
    {
        $this->process = $process;

        return $this;
    }

    public function getDataElements(): ?array
    {
        return $this->dataElements;
    }

    public function setDataElements(?array $dataElements): self
    {
        $this->dataElements = $dataElements;

        return $this;
    }

    public function getDataSubjects(): ?array
    {
        return $this->dataSubjects;
    }

    public function setDataSubjects(?array $dataSubjects): self
    {
        $this->dataSubjects = $dataSubjects;

        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(?string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    public function setResourceType(string $resourceType): self
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->resourceType;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getAccept(): ?string
    {
        return $this->accept;
    }

    public function setAccept(string $accept): self
    {
        $this->accept = $accept;

        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

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

    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getNotFound(): ?bool
    {
        return $this->notFound;
    }

    public function setNotFound(?bool $notFound): self
    {
        $this->notFound = $notFound;

        return $this;
    }

    public function getForbidden(): ?bool
    {
        return $this->forbidden;
    }

    public function setForbidden(?bool $forbidden): self
    {
        $this->forbidden = $forbidden;

        return $this;
    }

    public function getOk(): ?bool
    {
        return $this->ok;
    }

    public function setOk(?bool $ok): self
    {
        $this->ok = $ok;

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
