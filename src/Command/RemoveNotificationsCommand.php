<?php

namespace App\Command;

use App\Repository\NotificationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'notifications:remove',
    description: 'Remove Notifications linked to deleted entities',
)]
class RemoveNotificationsCommand extends Command
{
    public function __construct(
        private NotificationRepository $notificationRepo
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->notificationRepo->autoRemoveNotifications();

        $io = new SymfonyStyle($input, $output);
        $io->success("{$count} notifications removed");

        return Command::SUCCESS;
    }
}
