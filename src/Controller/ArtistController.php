<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Artist;
use App\Factory\ArtistFactory;
use App\Repository\ArtistRepository;
use App\Service\AuthSpotifyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType as SearchInputType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArtistController extends AbstractController
{
    private string $token;

    public function __construct(
        private readonly AuthSpotifyService $authSpotifyService,
        private readonly HttpClientInterface $httpClient,
        private readonly ArtistFactory $artistFactory
    ) {
        $this->token = $this->authSpotifyService->auth();
    }

    public function getArtist(string $id): Artist
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/artists/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ]
        ]);

        $data = $response->toArray();
        $imageUrl = $data['images'][0]['url'] ?? '';

        $artist = $this->artistFactory->createFromSpotifyData($data);
        $artist->setImageUrl($imageUrl);

        return $artist;
    }


    #[Route('/addartistfavorites', name: 'app_add_artist_favorites', methods: ['POST'])]
    public function addArtistFavorite(Request $request, ArtistRepository $artistRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $artistId = $request->request->get('id');

        $artist = $artistRepository->findOneBy(['id' => $artistId]);

        if (!$artist) {
            $artist = $this->getArtist($artistId);
            $em->persist($artist);
            $em->flush();
        }

        $user->addArtist($artist);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_artist_fav');
    }


    #[Route('/search/artistesFav', name: 'app_artist_fav')]
    public function artistesFav(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user) {
            $artists = $user->getAllArtists();
        } else {
            $artists = [];
        }

        return $this->render('artist/artistFav.html.twig', [
            'artistes' => $artists,
        ]);
    }


    #[Route('/search/artistes', name: 'app_artist_artistes')]
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

        return $this->render('artist/artistesSearch.html.twig', [
            'form' => $form->createView(),
            'artistes' => $artistes,
        ]);
    }

    #[Route('/delartistfavorites', name: 'app_del_artist_favorites', methods: ['POST'])]
    public function delArtistFavorite(Request $request, ArtistRepository $artistRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $artistId = $request->request->get('id');

        $artist = $artistRepository->findOneBy(['id' => $artistId]);

        if ($artist) {
            $user->removeArtist($artist);
            $em->persist($user);
            $em->flush();
        }

        return $this->redirectToRoute('app_artist_fav');
    }


}
