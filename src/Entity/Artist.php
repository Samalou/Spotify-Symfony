<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $spotifyUrl;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'artists')]
    private Collection $users;

    public function __construct(string $name, string $spotifyUrl)
    {
        $this->name = $name;
        $this->spotifyUrl = $spotifyUrl;
        $this->users = new ArrayCollection();
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSpotifyUrl(): string
    {
        return $this->spotifyUrl;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    // Setters
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setSpotifyUrl(string $spotifyUrl): self
    {
        $this->spotifyUrl = $spotifyUrl;

        return $this;
    }

    public function addArtist(Artist $artist): self
    {
        if (!$this->artists->contains($artist)) {
            $this->artists[] = $artist;
        }

        return $this;
    }

    public function removeArtist(Artist $artist): self
    {
        if ($this->artists->contains($artist)) {
            $this->artists->removeElement($artist);
        }

        return $this;
    }

    public function getArtists(): Collection
    {
        return $this->artists;
    }



    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addArtist($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeArtist($this);
        }

        return $this;
    }
}
