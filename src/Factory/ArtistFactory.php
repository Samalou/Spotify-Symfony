<?php
namespace App\Factory;

use App\Entity\Artist;

class ArtistFactory
{
    public function createFromSpotifyData(array $data): Artist
    {
        $name = $data['name'] ?? 'Unknown Artist';
        $spotifyUrl = $data['external_urls']['spotify'] ?? '';

        return new Artist($name, $spotifyUrl);
    }

    public function createMultipleFromSpotifyData(array $data): array
    {
        $artists = [];

        foreach ($data as $artistData) {
            $artists[] = $this->createFromSpotifyData($artistData);
        }

        return $artists;
    }
}
