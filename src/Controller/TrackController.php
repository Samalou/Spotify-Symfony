<?php

namespace App\Controller;

use App\Factory\TrackFactory;
use App\Service\AuthSpotifyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrackController extends AbstractController
{

    private string $token;


    public function __construct(private readonly AuthSpotifyService  $authSpotifyService,
                                private readonly HttpClientInterface $httpClient,
                                private readonly TrackFactory        $trackFactory
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }

    #[Route('/track', name: 'app_track_index')]
    public function index(): Response
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?query=juicewrld&type=track&locale=fr-FR', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $tracks = $this->trackFactory->createMultipleFromSpotifyData($response->toArray()['tracks']['items']);

        return $this->render('track/index.html.twig', [
            'tracks' => $tracks,
        ]);
    }
}
