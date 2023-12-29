<?php

namespace App\Controller;

use App\Repository\AbilityRepository;
use App\Repository\ActualityRepository;
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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SiteController extends AbstractController
{
    private string $hostname = '';

    // /**
    //  * @Route("/ads.txt", name="ads", defaults={"_format"="txt"})
    //  */
    // public function ads($venatusId)
    // {
    //     return $this->redirect("https://adstxt.venatusmedia.com/{$venatusId}_ads.txt");
    // }

    /**
     * @Route("/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function sitemap(
        Request $request,
        PokemonRepository $pokemonRepository,
        AbilityRepository $abilityRepository,
        ItemRepository $itemRepository,
        MoveRepository $moveRepository,
        TypeRepository $typeRepository,
        ActualityRepository $actualityRepository,
        GuideRepository $guideRepository,
        TournamentRepository $tournamentRepository,
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
        $tournaments = $tournamentRepository->findAll();
        $tiers = $tierRepository->findList($lastGen);

        $urls = [];
        $urls[] = [
            'loc' => $this->generateUrl('home'),
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
            // 'loc' => $this->generateUrl('teams'),
            'loc' => $this->generateUrl('home') . 'entity/teams',
            'lastmod' =>  $teamsDate ? ($teamsDate)->format('Y-m-d') : $dateNow,
            'priority' => '1.00'
        ];
        $urls[] = [
            'loc' => $this->generateUrl('home') . 'entity/teams/create',
            'lastmod' => '2022-05-27',
            'priority' => '0.80'
        ];

        $topTeam = $teamRepository->getLastTopWeek();
        if (!is_null($topTeam)) {
            $urls[] = [
                'loc' => $this->generateUrl('home') . 'entity/teams/top',
                'lastmod' => $topTeam->getDateCreation()->format('Y-m-d'),
                'priority' => '0.80'
            ];
        }

        $actualitiesDate = null;
        if (count($actualities)) {
            $actualitiesDate = $actualities[0]->getDateCreation();
        }
        $urls[] = [
            'loc' => $this->generateUrl('actualities'),
            'lastmod' => $actualitiesDate ? ($actualitiesDate)->format('Y-m-d') : null,
            'priority' => '0.90'
        ];

        $guidesDate = null;
        if (count($guides)) {
            $guidesDate = $guides[0]->getDateCreation();
        }
        $urls[] = [
            'loc' => $this->generateUrl('guides'),
            'lastmod' => $guidesDate ? ($guidesDate)->format('Y-m-d') : null,
            'priority' => '0.80'
        ];

        $tournamentsDate = null;
        if (count($tournaments)) {
            $tournamentsDate = $tournaments[0]->getDateCreation();
        }
        $urls[] = [
            'loc' => $this->generateUrl('tournaments'),
            'lastmod' => $tournamentsDate ? ($tournamentsDate)->format('Y-m-d') : null,
            'priority' => '0.80'
        ];

        foreach ($pokemons as $pokemon) {
            $urls[] = [
                'loc' => $this->generateUrl('pokemon_by_name', ['name' => $pokemon->getName()]),
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
                'loc' => $this->generateUrl('team', ['id' => $team->getId()]),
                // first day of the month
                'lastmod' => $team->getUpdateDate() ? $team->getUpdateDate()->format('Y-m-d') : $team->getDateCreation()->format('Y-m-d'),
                'priority' => '0.90'
            ];
        }

        foreach ($actualities as $actuality) {
            $image = null;
            if (count($actuality->getImages())) {
                $image = [
                    'loc' => $this->generateImgUrl('actualities/' . $actuality->getImages()[0]),
                    'title' => $actuality->getTitle(),
                ];
            }
            $urls[] = [
                'loc' => $this->generateUrl('actuality', ['id' => $actuality->getId()]),
                'lastmod' => $actuality->getDateCreation()->format('Y-m-d'),
                'priority' => '0.80',
                'image' => $image
            ];
        }

        foreach ($guides as $guide) {
            $image = null;
            if (count($guide->getImages())) {
                $image = [
                    'loc' => $this->generateImgUrl('guides/' . $guide->getImages()[0]),
                    'title' => $guide->getTitle(),
                ];
            }
            $urls[] = [
                'loc' => $this->generateUrl('guide', ['id' => $guide->getId()]),
                'lastmod' => $guide->getDateCreation()->format('Y-m-d'),
                'priority' => '0.80',
                'image' => $image
            ];
        }

        foreach ($tournaments as $tournament) {
            $image = null;
            if (count($tournament->getImages())) {
                $image = [
                    'loc' => $this->generateImgUrl('tournaments/' . $tournament->getImages()[0]),
                    'title' => $tournament->getTitle(),
                ];
            }
            $urls[] = [
                'loc' => $this->generateUrl('tournament', ['id' => $tournament->getId()]),
                'lastmod' => $tournament->getDateCreation()->format('Y-m-d'),
                'priority' => '0.80',
                'image' => $image
            ];
        }

        foreach ($items as $item) {
            $urls[] = [
                'loc' => $this->generateUrl('item', ['id' => $item->getId()]),
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
                'loc' => $this->generateUrl('ability', ['id' => $ability->getId()]),
                // first day of the month
                'lastmod' => $monthDate,
                'priority' => '0.90'
            ];
        }

        foreach ($moves as $move) {
            $urls[] = [
                'loc' => $this->generateUrl('move', ['id' => $move->getId()]),
                // first day of the month
                'lastmod' => $monthDate,
                'priority' => '0.90'
            ];
        }

        foreach ($types as $type) {
            $urls[] = [
                'loc' => $this->generateUrl('type', ['id' => $type->getId()]),
                // first day of the month
                'lastmod' => '2022-05-27',
                'priority' => '0.90'
            ];
        }

        $urls[] = [
            // 'loc' => $this->generateUrl('tiers'),
            'loc' => $this->generateUrl('home') . 'entity/tiers',
            'lastmod' => $monthDate,
            'priority' => '0.70'
        ];
        foreach ($tiers as $tier) {
            $urls[] = [
                'loc' => $this->generateUrl('tier', ['id' => $tier->getId()]),
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
            'loc' => $this->generateUrl('home') . 'videos',
            'lastmod' => '2022-07-28',
            'priority' => '0.20'
        ];
        $urls[] = [
            'loc' => $this->generateUrl('home') . 'remerciements',
            'lastmod' => '2022-05-27',
            'priority' => '0.10'
        ];
        $urls[] = [
            'loc' => $this->generateUrl('home') . 'cgu',
            'lastmod' => '2022-05-27',
            'priority' => '0.10'
        ];
        $urls[] = [
            'loc' => $this->generateUrl('home') . 'mentions-legales',
            'lastmod' => '2022-05-27',
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

    protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->hostname . parent::generateUrl($route, $parameters, $referenceType);
    }

    protected function generateImgUrl(string $path): string
    {
        return $this->hostname . '/images/' . $path;
    }
}
