<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Track
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private string $id;


    #[ORM\Column(type: 'integer')]
    private int $discNumber;

    #[ORM\Column(type: 'integer')]
    private int $durationMs;

    #[ORM\Column(type: 'boolean')]
    private bool $explicit;

    #[ORM\Column(type: 'string', length: 12)]
    private string $isrc;

    #[ORM\Column(type: 'string')]
    private string $spotifyUrl;

    #[ORM\Column(type: 'string')]
    private string $href;

    #[ORM\Column(type: 'boolean')]
    private bool $isLocal;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $popularity;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $previewUrl;

    #[ORM\Column(type: 'string')]
    private string $trackNumber;

    #[ORM\Column(type: 'string')]
    private string $type;

    #[ORM\Column(type: 'string')]
    private string $uri;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $pictureLink;


    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'tracks')]
    private Collection $users;


    public function __construct(
        int $discNumber,
        int $durationMs,
        bool $explicit,
        string $isrc,
        string $spotifyUrl,
        string $href,
        bool $isLocal,
        string $name,
        string $popularity,
        ?string $previewUrl,
        string $trackNumber,
        string $type,
        string $uri,
        ?string $pictureLink
    ) {
        $this->discNumber = $discNumber;
        $this->durationMs = $durationMs;
        $this->explicit = $explicit;
        $this->isrc = $isrc;
        $this->spotifyUrl = $spotifyUrl;
        $this->href = $href;
        $this->isLocal = $isLocal;
        $this->name = $name;
        $this->popularity = $popularity;
        $this->previewUrl = $previewUrl;
        $this->trackNumber = $trackNumber;
        $this->type = $type;
        $this->uri = $uri;
        $this->pictureLink = $pictureLink;
        $this->users = new ArrayCollection();
    }


    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getDiscNumber(): int
    {
        return $this->discNumber;
    }

    public function getDurationMs(): int
    {
        return $this->durationMs;
    }

    public function isExplicit(): bool
    {
        return $this->explicit;
    }

    public function getIsrc(): string
    {
        return $this->isrc;
    }

    public function getSpotifyUrl(): string
    {
        return $this->spotifyUrl;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function isLocal(): bool
    {
        return $this->isLocal;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPopularity(): int
    {
        return $this->popularity;
    }

    public function getPreviewUrl(): ?string
    {
        return $this->previewUrl;
    }

    public function getTrackNumber(): int
    {
        return $this->trackNumber;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPictureLink(): ?string
    {
        return $this->pictureLink;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }
}
