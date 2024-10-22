<?php

namespace App\Controller;

use App\Entity\Track;
use App\Factory\TrackFactory;
use App\Service\AuthSpotifyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType as SearchInputType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Repository\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;



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

    public function getTrack(string $id): Track
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/tracks/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ]
        ]);

        $track = $this->trackFactory->createFromSpotifyData($response->toArray());
        return $track;
    }

    #[Route('/addfavorites', name: 'app_add_favorites', methods: ['POST'])]
    public function addFavorite(Request $request, TrackRepository $trackRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $trackId = $request->request->get('id');
        $track = $trackRepository->findOneBy(['id' => $trackId]);

        if (!$track) {
            $track = $this->getTrack($trackId);
            $em->persist($track);
            $em->flush();
        }

        $user->addTrack($track);

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_favorites');
    }


    #[Route('/delfavorites', name: 'app_del_favorites', methods: ['POST'])]
    public function delFavorite(Request $request, TrackRepository $trackRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $trackId = $request->request->get('id');
        $track = $trackRepository->findOneBy(['id' => $trackId]);

        if ($track && $user->getAllTracks()->contains($track)) {
            $user->removeTrack($track);

            if ($track->getUsers()->isEmpty()) {
                $em->remove($track);
            }

            $em->persist($user);
            $em->flush();
        }

        return $this->redirectToRoute('app_favorites');
    }







    #[Route('/favorites', name: 'app_favorites')]
    public function favorites(Request $request): Response
    {
        $user = $this->getUser();
        $tracks = $user ? $user->getAllTracks() : [];

        return $this->render('track/favorites.html.twig', [
            'tracks' => $tracks,
        ]);
    }

    #[Route('/track/{id}', name: 'app_track_information')]
    public function information(string $id): Response
    {
        $track = $this->getTrack($id);
        $recommendations = [];

        $spotifyUrl = $track->getSpotifyUrl();
        $spotifyId = basename($spotifyUrl);


        if ($track) {
            $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/recommendations', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'query' => [
                    'seed_tracks' => $spotifyId,
                    'limit' => 12,
                ],
            ]);

            $data = $response->toArray();
            $recommendations = $this->trackFactory->createMultipleFromSpotifyData($data['tracks']);
        }

        return $this->render('track/information.html.twig', [
            'track' => $track,
            'recommendations' => $recommendations,
        ]);
    }


    #[Route('/search/artistes', name: 'app_track_artistes')]
    public function artistesSearch(Request $request): Response
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

        $artistes = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $query = $form->get('query')->getData();

            $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'query' => [
                    'q' => $query,
                    'type' => 'artist',
                    'locale' => 'fr-FR',
                ],
            ]);

            $data = $response->toArray();
            $artistes = $data['artists']['items'];
        }

        $artistes = array_slice($artistes, 0, 12);

        return $this->render('track/artistesSearch.html.twig', [
            'form' => $form->createView(),
            'artistes' => $artistes,
        ]);
    }


    #[Route('/search/musique', name: 'app_track_index')]
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

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->redirectToRoute('app_track_index');
    }

}
