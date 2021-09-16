<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LinkRepository::class)
 */
class Link
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Url()
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $uri;

    /**
     * @ORM\Column(type="integer")
     */
    private $redirectCount;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $preview;

    /**
     * @ORM\Column(type="integer")
     */
    private $previewAttempts;

    function __construct(){
        $this->redirectCount = 0;
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->previewAttempts = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        $this->uri = substr(md5(microtime(true)), 0, 9);

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getRedirectCount(): ?int
    {
        return $this->redirectCount;
    }

    public function setRedirectCount(int $redirectCount): self
    {
        $this->redirectCount = $redirectCount;

        return $this;
    }

    public function __toString(){
        return (string)$this->url;
    }

    public function incrementCount(){
        $this->redirectCount++;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created): void
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated): void
    {
        $this->updated = $updated;
    }

    /**
     * @return mixed
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @param mixed $preview
     */
    public function setPreview($preview): void
    {
        $this->preview = $preview;
    }

    /**
     * @return int
     */
    public function getPreviewAttempts(): int
    {
        return $this->previewAttempts;
    }

    /**
     * @param int $previewAttempts
     */
    public function setPreviewAttempts(int $previewAttempts): void
    {
        $this->previewAttempts = $previewAttempts;
    }

    public function incrementPreviewAttempt(): void {
        $this->previewAttempts++;
    }
}
