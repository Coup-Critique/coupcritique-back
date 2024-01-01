<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

#[AsCommand(
    name: 'twig:preview',
    description: 'Compile twig to html into console.'
)]
class TwigPreviewer extends Command
{
    public function __construct(private readonly Environment $twig)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('template_name', InputArgument::REQUIRED, "the path of the twig template")
            ->addArgument('parameters', InputArgument::OPTIONAL, "the required arguments in JSON");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('template_name');
        $parse_json = json_decode($input->getArgument('parameters'), true);
        $html = $this->twig->render($arg1, is_null($parse_json) ? [] : $parse_json);

        $output->writeln($html);

        return 0;
    }
}
