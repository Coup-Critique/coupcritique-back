<?php

namespace App\Controller;

use App\Repository\AbilityRepository;
use App\Repository\ActualityRepository;
use App\Repository\CircuitArticleRepository;
use App\Repository\CircuitTourRepository;
use App\Repository\GuideRepository;
use App\Repository\ItemRepository;
use App\Repository\MoveRepository;
use App\Repository\PokemonRepository;
use App\Repository\TeamRepository;
use App\Repository\TierRepository;
use App\Repository\TournamentRepository;
use App\Repository\TypeRepository;
use App\Service\GenRequestManager;
use App\Service\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SiteController extends AbstractController
{
    private string $hostname = '';

    #[Route(path: '/api', name: 'api_home')]
    public function apiHome(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route(path: '/sitemap.xml', name: 'sitemap', defaults: ['_format' => 'xml'])]
    public function sitemap(
        Request $request,
        PokemonRepository $pokemonRepository,
        AbilityRepository $abilityRepository,
        ItemRepository $itemRepository,
        MoveRepository $moveRepository,
        TypeRepository $typeRepository,
        ActualityRepository $actualityRepository,
        GuideRepository $guideRepository,
        // TournamentRepository $tournamentRepository,
        CircuitTourRepository $circuitTourRepository,
        CircuitArticleRepository $circuitArticleRepository,
        TeamRepository $teamRepository,
        TierRepository $tierRepository
    ) {
        $this->hostname = $request->getSchemeAndHttpHost();

        $lastGen = GenRequestManager::getLastGen();
        $dateNow = (new \DateTime())->format('Y-m-d');
        $monthDate = (new \DateTime())->format('Y-m') . '-01';

        $pokemons = $pokemonRepository->findAllWithGen($lastGen);
        $abilities = $abilityRepository->findBy(['gen' => $lastGen]);
        $items = $itemRepository->findBy(['gen' => $lastGen]);
        $moves = $moveRepository->findBy(['gen' => $lastGen]);
        $types = $typeRepository->findBy(['gen' => $lastGen]);
        $teams = $teamRepository->findBy([], ['date_creation' => 'DESC']);
        $actualities = $actualityRepository->findAll();
        $guides = $guideRepository->findAll();
        // $tournaments = $tournamentRepository->findAll();
        $tours = $circuitTourRepository->findAll();
        $circuitArticles = $circuitArticleRepository->findAll();
        $tiers = $tierRepository->findList($lastGen);

        $urls = [];
        $urls[] = [
            'loc' => $this->generateFrontUrl('/'),
            'lastmod' => $dateNow,
            'priority' => '1.00',
            'image' => [
                'loc' => $this->generateImgUrl('keldeo-landorus.png'),
                'title' => 'Illustration de Coup Critique',
            ]
        ];

        $teamsDate = null;
        if (count($teams)) {
            $teamsDate = $teams[0]->getDateCreation();
        }
        $urls[] = [
            'loc' => $this->generateFrontUrl('/entity/teams'),
            'lastmod' =>  $teamsDate ? ($teamsDate)->format('Y-m-d') : $dateNow,
            'priority' => '1.00'
        ];
        $urls[] = [
            'loc' => $this->generateFrontUrl('/entity/teams/create'),
            'lastmod' => '2024-09-16',
            'priority' => '0.80'
        ];

        $topTeam = $teamRepository->getLastTopWeek();
        if ($topTeam != null) {
            $urls[] = [
                'loc' => $this->generateFrontUrl('/entity/teams/top'),
                'lastmod' => $topTeam->getDateCreation()->format('Y-m-d'),
                'priority' => '0.80'
            ];
        }

        $urls[] = $this->generateArticles($actualities, 'actualities');
        $urls[] = $this->generateArticles($guides, 'guides');
        // $urls[]= $this->generateArticles($tournaments, 'tournaments');
        $urls[] = $this->generateArticles($tours, 'circuit-tours');
        $urls[] = $this->generateArticles($circuitArticles, 'circuit-articles');

        foreach ($pokemons as $pokemon) {
            $urls[] = [
                'loc' => $this->generateFrontUrl('/entity/pokemons/' . $pokemon->getId()),
                // first day of the month
                'lastmod' => $monthDate,
                'priority' => '1.00',
                'image' => [
                    'loc' => $this->generateImgUrl('pokemons/' . Utils::formatFileName($pokemon->getName()) . '.png'),
                    'title' => $pokemon->getNom() ?: $pokemon->getName(),
                ]
            ];
        }

        foreach ($teams as $team) {
            $urls[] = [
                'loc' => $this->generateFrontUrl('/entity/teams/' . $team->getId()),
                'lastmod' => $team->getUpdateDate() ? $team->getUpdateDate()->format('Y-m-d') : $team->getDateCreation()->format('Y-m-d'),
                'priority' => '0.90'
            ];
        }

        $urls += $this->generateAnArticle($actualities, 'actualities');
        $urls += $this->generateAnArticle($guides, 'guides');
        // $urls+= $this->generateAnArticle($tournaments, 'tournaments');
        $urls += $this->generateAnArticle($tours, 'circuit-tours');
        $urls += $this->generateAnArticle($circuitArticles, 'circuit-articles');

        foreach ($items as $item) {
            $urls[] = [
                'loc' => $this->generateFrontUrl('/entity/items/' . $item->getId()),
                // first day of the month
                'lastmod' => $monthDate,
                'priority' => '1.00',
                'image' => [
                    'loc' => $this->generateImgUrl('items/' . Utils::formatFileName($item->getName()) . '.png'),
                    'title' => $item->getNom() ?: $item->getName(),
                ]
            ];
        }

        foreach ($abilities as $ability) {
            $urls[] = [
                'loc' => $this->generateFrontUrl('/entity/abilities/' . $ability->getId()),
                // first day of the month
                'lastmod' => $monthDate,
                'priority' => '0.90'
            ];
        }

        foreach ($moves as $move) {
            $urls[] = [
                'loc' => $this->generateFrontUrl('/entity/moves/' . $move->getId()),
                // first day of the month
                'lastmod' => $monthDate,
                'priority' => '0.90'
            ];
        }

        foreach ($types as $type) {
            $urls[] = [
                'loc' => $this->generateFrontUrl('/entity/types/' . $type->getId()),
                'lastmod' => '2024-09-16',
                'priority' => '0.90'
            ];
        }

        $urls[] = [
            'loc' => $this->generateFrontUrl('/entity/tiers'),
            'lastmod' => $monthDate,
            'priority' => '0.70'
        ];
        foreach ($tiers as $tier) {
            $urls[] = [
                'loc' => $this->generateFrontUrl('/entity/tiers/' . $tier->getId()),
                // first day of the month
                'lastmod' => $monthDate,
                'priority' => '1.00',
                'image' => [
                    'loc' => $this->generateImgUrl('tiers/' . "$lastGen-" . ($tier->getShortName() ?: ucfirst($tier->getName())) . '.png'),
                    'title' => $tier->getName(),
                ]
            ];
        }

        $urls[] = [
            'loc' => $this->generateFrontUrl('/videos'),
            'lastmod' => $monthDate,
            'priority' => '0.20'
        ];
        $urls[] = [
            'loc' => $this->generateFrontUrl('/remerciements'),
            'lastmod' => '2024-09-16',
            'priority' => '0.10'
        ];
        $urls[] = [
            'loc' => $this->generateFrontUrl('/cgu'),
            'lastmod' => '2024-09-16',
            'priority' => '0.10'
        ];
        $urls[] = [
            'loc' => $this->generateFrontUrl('/mentions-legales'),
            'lastmod' => '2024-09-16',
            'priority' => '0.10'
        ];

        $response = new Response(
            $this->renderView(
                'sitemap.html.twig',
                ['urls' => $urls]
            ),
            200
        );
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

    protected function generateFrontUrl(string $route): string
    {
        return $this->hostname . $route;
    }

    protected function generateImgUrl(string $path): string
    {
        return $this->hostname . '/images/' . $path;
    }

    protected function generateArticles(array $articles, string $route)
    {
        $date = null;
        if (count($articles)) {
            $date = $articles[0]->getDateCreation();
        }
        return [
            'loc' => $this->generateFrontUrl("/entity/$route"),
            'lastmod' => $date ? ($date)->format('Y-m-d') : null,
            'priority' => '0.80'
        ];
    }

    protected function generateAnArticle(array $articles, string $route)
    {
        $urls = [];
        foreach ($articles as $article) {
            $image = null;
            if (count($article->getImages())) {
                $image = [
                    'loc' => $this->generateImgUrl("uploads/$route/" . $article->getImages()[0]),
                    'title' => $article->getTitle(),
                ];
            }
            $urls[] = [
                'loc' => $this->generateFrontUrl("/entity/$route" . $article->getId()),
                'lastmod' => $article->getDateCreation()->format('Y-m-d'),
                'priority' => '0.80',
                'image' => $image
            ];
        }
        return $urls;
    }
}
