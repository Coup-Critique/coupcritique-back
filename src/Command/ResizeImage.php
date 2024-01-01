<?php

namespace App\Command;

use App\Service\ImageManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'resize:image',
    description: 'Add a short description for your command.'
)]
class ResizeImage extends Command
{
    protected $image_manager;

    public function __construct(ImageManager $image_manager)
    {
        $this->image_manager = $image_manager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::OPTIONAL, 'Argument description')
            ->addArgument('destination', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = $input->getArgument('source');
        $destination = $input->getArgument('destination');

        if (!$source) {
            $io->error("path is not specified \n ex: php bin/console trim:images -- /public/images/pokemons/ /public/images/pokemons/result");
            return Command::FAILURE;
        }

        if (!$destination) {
            $io->error("path is not specified \n ex: php bin/console trim:images -- /public/images/pokemons/ /public/images/pokemons/result");
            return Command::FAILURE;
        }

        $this->image_manager->resizeImages($source, $destination);
        return Command::SUCCESS;
    }
}
