<?php

namespace App\Command;

use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BanTeams extends Command
{
    protected static $defaultName = 'ban:teams';


    public function __construct(
        private readonly TeamRepository $repo,
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Ban Teams with a banned Pokemon ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $teams = $this->repo->findAllCommand();
        foreach ($teams as $team) {
            $tier = $team->getTier();
            $rank = $tier->getRank();
            if ($tier->getShortName() === "ZU") $rank--;
            foreach ($team->getPokemonInstances() as $instance) {
                $pokemon = $instance->getPokemon();
                $pokemonTierRank = $pokemon->getTier() == null
                    ? null
                    : $pokemon->getTier()->getRank();
                if (
                    (
                        $pokemon->getTier() == null
                        || $pokemon->getTier()->getName() === 'Untiered'
                    ) || (
                        $rank != null
                        && $pokemonTierRank != null
                        && $pokemonTierRank < $rank
                        && $rank < 50
                    )
                ) {
                    $team->setBanned(true);
                    $io->info("ban {$team->getId()} {$tier->getName()} {$pokemon->getName()} {$pokemon->getTier()->getName()}");
                }
            }
        }


        $this->em->flush();

        return Command::SUCCESS;
    }
}
