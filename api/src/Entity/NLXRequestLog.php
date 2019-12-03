<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NLXRequestLogRepository")
 */
class NLXRequestLog
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
    private $applicationId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $requestId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subjectId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $processId;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $dataElements = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $dataSubjects = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $objectId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $objectClass;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $route;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $endpoint;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $method;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $contentType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $session;

    /**
     * @ORM\Column(type="datetime")
     */
    private $loggedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplicationId(): ?string
    {
        return $this->applicationId;
    }

    public function setApplicationId(?string $applicationId): self
    {
        $this->applicationId = $applicationId;

        return $this;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function setRequestId(?string $requestId): self
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getSubjectId(): ?string
    {
        return $this->subjectId;
    }

    public function setSubjectId(?string $subjectId): self
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function getProcessId(): ?string
    {
        return $this->processId;
    }

    public function setProcessId(?string $processId): self
    {
        $this->processId = $processId;

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

    public function getObjectId(): ?string
    {
        return $this->objectId;
    }

    public function setObjectId(?string $objectId): self
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    public function setObjectClass(string $objectClass): self
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
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

    public function getSession(): ?string
    {
        return $this->session;
    }

    public function setSession(string $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getLoggedAt(): ?\DateTimeInterface
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(\DateTimeInterface $loggedAt): self
    {
        $this->loggedAt = $loggedAt;

        return $this;
    }
}
