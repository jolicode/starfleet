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

    public const SOURCE_MANUAL = 'manual';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(name="remote_id", type="string", length=255, nullable=true)
     */
    private ?string $remoteId = null;

    /**
     * @ORM\Column(name="source", type="string", length=20)
     */
    private string $source = self::SOURCE_MANUAL;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(name="slug", type="string", length=255, nullable=false, unique=true)
     * @Gedmo\Slug(fields={"name"})
     */
    private string $slug;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(name="start_at", type="datetime")
     */
    private \DateTimeInterface $startAt;

    /**
     * @ORM\Column(name="end_at", type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $endAt = null;

    /**
     * @ORM\Column(name="cfp_url", type="string", length=255, nullable=true)
     */
    private ?string $cfpUrl = null;

    /**
     * @ORM\Column(name="cfp_end_at", type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $cfpEndAt = null;

    /**
     * @ORM\Column(name="site_url", type="string", length=255, nullable=true)
     */
    private ?string $siteUrl = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Submit", mappedBy="conference")
     *
     * @var Collection<Submit>
     */
    private Collection $submits;

    /**
     * @ORM\Column(name="article_url", type="text", nullable=true)
     */
    private ?string $articleUrl = null;

    /**
     * @ORM\Column(name="excluded", type="boolean", options={"default"=0})
     */
    private bool $excluded = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Participation", mappedBy="conference", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Collection<Participation>
     */
    private Collection $participations;

    /**
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private ?string $country = null;

    /**
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private ?string $city = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $online = false;

    /**
     * @ORM\Column(type="jsonb")
     *
     * @var array<string>
     */
    private array $tags = [];

    /**
     * @ORM\Column(type="jsonb", nullable=true)
     *
     * @var array<float>|null
     */
    private ?array $coordinates = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $featured = false;

    public function __construct()
    {
        $this->submits = new ArrayCollection();
        $this->participations = new ArrayCollection();
    }

    public function __toString(): string
    {
        $startYear = $this->getStartAt() ? $this->getStartAt()->format('Y') : '';

        return trim(sprintf('%s %s', $this->getName(), "({$startYear})")) ?? '';
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

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setName(string $name): self
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

    public function setStartAt(\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
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

    public function setCfpEndAt(?\DateTimeInterface $cfpEndAt): self
    {
        $this->cfpEndAt = $cfpEndAt;

        return $this;
    }

    public function getCfpEndAt(): ?\DateTimeInterface
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

    public function addSubmit(Submit $submit): self
    {
        if (!$this->submits->contains($submit)) {
            $this->submits[] = $submit;
            $submit->setConference($this);
        }

        return $this;
    }

    public function removeSubmit(Submit $submit): self
    {
        if ($this->submits->contains($submit)) {
            $this->submits->removeElement($submit);
        }

        return $this;
    }

    /**
     * @return Collection|Submit[]
     */
    public function getSubmits(): Collection
    {
        return $this->submits;
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

    /**
     * @return Collection|Participation[]
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): self
    {
        if (!$this->participations->contains($participation)) {
            $this->participations[] = $participation;
            $participation->setConference($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): self
    {
        if ($this->participations->contains($participation)) {
            $this->participations->removeElement($participation);
        }

        return $this;
    }

    public function getExcluded(): ?bool
    {
        return $this->excluded;
    }

    public function setExcluded(bool $excluded): self
    {
        $this->excluded = $excluded;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    /**
     * @param array<string> $tags
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function addTag(string $tag): self
    {
        $this->tags[] = $tag;

        return $this;
    }

    /** @return array<string> */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function guessConferenceType(): string
    {
        if ($this->excluded) {
            return 'ExcludedConference';
        }

        if ($this->endAt < new \DateTime()) {
            return 'PassedConference';
        }

        return 'NextConference';
    }

    /** @return array<float>|null */
    public function getCoordinates(): ?array
    {
        return $this->coordinates;
    }

    /** @param array<float>|null $coordinates */
    public function setCoordinates(?array $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function getFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;

        return $this;
    }
}
