<?php

namespace App\Factory;

use App\Entity\Track;

class TrackFactory
{
    public function createFromSpotifyData(array $spotifyData): Track
    {
        return new Track(
            $spotifyData['disc_number'],
            $spotifyData['duration_ms'],
            $spotifyData['explicit'],
            $spotifyData['external_ids']['isrc'] ?? '',
            $spotifyData['external_urls']['spotify'],
            $spotifyData['href'],
            (bool)$spotifyData['is_local'],
            $spotifyData['name'],
            (int)$spotifyData['popularity'],
            $spotifyData['preview_url'] ?? null,
            (string)($spotifyData['track_number'] ?? ''),
            $spotifyData['type'],
            $spotifyData['uri'],
            $spotifyData['album']['images'][0]['url'] ?? null
        );
    }

    public function createMultipleFromSpotifyData(array $spotifyTracksData): array
    {
        $tracks = [];
        foreach ($spotifyTracksData as $spotifyTrackData) {
            $tracks[] = $this->createFromSpotifyData($spotifyTrackData);
        }
        return $tracks;
    }
}
