<?php

namespace App\Controller;

use App\Factory\TrackFactory;
use App\Service\AuthSpotifyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType as SearchInputType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrackController extends AbstractController
{
    private string $token;

    public function __construct(
        private readonly AuthSpotifyService $authSpotifyService,
        private readonly HttpClientInterface $httpClient,
        private readonly TrackFactory $trackFactory
    ) {
        $this->token = $this->authSpotifyService->auth();
    }

    #[Route('/track', name: 'app_track_index')]
    public function index(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('query', SearchInputType::class, [
                'attr' => ['placeholder' => 'Rechercher'],
                'label' => false,
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => ['class' => 'submit'],
            ])
            ->getForm();

        $form->handleRequest($request);

        $tracks = [];

        if ($form->isSubmitted() && $form->isValid()) {

            $query = $form->get('query')->getData();

            $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'query' => [
                    'q' => $query,
                    'type' => 'track',
                    'locale' => 'fr-FR',
                ],
            ]);

            $tracks = $this->trackFactory->createMultipleFromSpotifyData($response->toArray()['tracks']['items']);

            $tracks = array_slice($tracks, 0, 12);
        }

        return $this->render('track/index.html.twig', [
            'form' => $form->createView(),
            'tracks' => $tracks,
        ]);
    }
}
