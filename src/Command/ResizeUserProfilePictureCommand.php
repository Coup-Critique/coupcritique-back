<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\FileManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:resize-user-profile-picture',
    description: 'Transform profile pictures into centered squares',
)]
class ResizeUserProfilePictureCommand extends Command
{
    public function __construct(
        private FileManager $fileManager,
        private UserRepository $userRepository
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $totalUsers = (int) $this->userRepository->createQueryBuilder('u')
                           ->select('count(u)')
                           ->where('u.picture IS NOT NULL')
                           ->getQuery()
                           ->getSingleScalarResult();

        $query = $this->userRepository->createQueryBuilder('u')
                       ->select('u.picture')
                       ->where('u.picture IS NOT NULL')
                       ->getQuery();


        /**
        * @var string $imageFileName 
        */
        foreach ($io->progressIterate($query->toIterable(), $totalUsers) as $imageFileName) {

            $imageFileName = $imageFileName['picture'];            
            $imageFullPath = __DIR__."/../../public/images/uploads/users/200px/$imageFileName";

            if (!is_file($imageFullPath)) {
                continue;
            }

            $this->fileManager->imageCropCenter("./images/uploads/users/200px/$imageFileName");
        }

        return Command::SUCCESS;
    }
}
