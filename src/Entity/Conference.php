<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="conference")
 * @ORM\Entity(repositoryClass="App\Repository\ConferenceRepository")
 */
class Conference
{
    use TimestampableEntity;

    const SOURCE_SALOON = 'saloon';
    const SOURCE_CONFS_TECH = 'conf-tech';
    const SOURCE_MANUAL = 'manual';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="remote_id", type="string", length=255, nullable=true)
     */
    private $remoteId;

    /**
     * @ORM\Column(name="hash", type="string", length=255, nullable=true)
     */
    private $hash;

    /**
     * @ORM\Column(name="source", type="string", length=20)
     */
    private $source = self::SOURCE_MANUAL;

    /**
     * @ORM\Column(name="slug", type="string", length=255, nullable=false, unique=true)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(name="location", type="string", length=255)
     */
    private $location;

    /**
     * @ORM\Column(name="start_at", type="datetime")
     */
    private $startAt;

    /**
     * @ORM\Column(name="end_at", type="datetime")
     */
    private $endAt;

    /**
     * @ORM\Column(name="cfp_url", type="string", length=255, nullable=true)
     */
    private $cfpUrl;

    /**
     * @ORM\Column(name="cfp_end_at", type="datetime", nullable=true)
     */
    private $cfpEndAt;

    /**
     * @ORM\Column(name="site_url", type="string", length=255, nullable=true)
     */
    private $siteUrl;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Submit", mappedBy="conference")
     */
    private $submits;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="conferences")
     * @ORM\JoinTable(name="conferences_tags")
     */
    private $tags;

    /**
     * @ORM\Column(name="article_url", type="text", nullable=true)
     */
    private $articleUrl;

    /**
     * TODO: remove when attendees tracking is implemented.
     *
     * @ORM\Column(name="attended", type="boolean")
     */
    private $attended = false;

    public function __construct()
    {
        $this->submits = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setRemoteId(?string $remoteId): self
    {
        $this->remoteId = $remoteId;

        return $this;
    }

    public function getRemoteId(): ?string
    {
        return $this->remoteId;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setHash($hash): void
    {
        $this->hash = $hash;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
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

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setStartAt(?\DateTime $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getStartAt(): ?\DateTime
    {
        return $this->startAt;
    }

    public function setEndAt(?\DateTime $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getEndAt(): ?\DateTime
    {
        return $this->endAt;
    }

    public function setCfpUrl(?string $cfpUrl): self
    {
        $this->cfpUrl = $cfpUrl;

        return $this;
    }

    public function getCfpUrl(): ?string
    {
        return $this->cfpUrl;
    }

    public function setCfpEndAt(?\DateTime $cfpEndAt): self
    {
        $this->cfpEndAt = $cfpEndAt;

        return $this;
    }

    public function getCfpEndAt(): ?\DateTime
    {
        return $this->cfpEndAt;
    }

    public function getSiteUrl(): ?string
    {
        return $this->siteUrl;
    }

    public function setSiteUrl(?string $siteUrl): self
    {
        $this->siteUrl = $siteUrl;

        return $this;
    }

    public function addSubmit(Submit $submits): self
    {
        $this->submits[] = $submits;

        return $this;
    }

    public function removeSubmit(Submit $submits): self
    {
        $this->submits->removeElement($submits);

        return $this;
    }

    public function getSubmits(): ?Collection
    {
        return $this->submits;
    }

    public function addTag(Tag $tag): self
    {
        $this->tags[] = $tag;

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getTags(): ?Collection
    {
        return $this->tags;
    }

    public function setArticleUrl(?string $articleUrl): self
    {
        $this->articleUrl = $articleUrl;

        return $this;
    }

    public function getArticleUrl(): ?string
    {
        return $this->articleUrl;
    }

    public function setAttended(bool $attended): self
    {
        $this->attended = $attended;

        return $this;
    }

    public function isAttended(): bool
    {
        return $this->attended;
    }

    public function __toString(): string
    {
        return $this->name ?? (string) $this->id;
    }
}
