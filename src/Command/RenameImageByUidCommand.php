<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Abstracts\AbstractArticle;
use App\Entity\Actuality;
use App\Entity\DrivedFile;
use App\Entity\Guide;
use App\Entity\Tournament;
use App\Entity\User;
use App\Repository\Abstracts\AbstractArticleRepository;
use App\Repository\DrivedFileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:rename-image-by-uid',
    description: 'Rename all image files by their uid',
)]
class RenameImageByUidCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private string $publicPath;

    private const BATCH = 100;

    public function __construct(EntityManagerInterface $entityManager, string $publicPath)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->publicPath = $publicPath;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->renameUserImgProfiles($io);
        $this->renameDriveFilenames($io);
        
        $this->renameArticleImages($io, "actuality", Actuality::class, $this->publicPath."/images/uploads/actualities");
        $this->renameArticleImages($io, "guide", Guide::class, $this->publicPath."/images/uploads/guides");
        $this->renameArticleImages($io, "tournament", Tournament::class, $this->publicPath."/images/uploads/tournaments");
        

        return Command::SUCCESS;
    }

    private function renameUserImgProfiles(SymfonyStyle $io): void {
        $io->info('Renaming users image profile');
        /**
         * @var UserRepository $repository
         */
        $repository = $this->entityManager->getRepository(User::class);

        $queryBuilder = $repository->createQueryBuilder('u')
                                   ->where('u.picture IS NOT NULL');
        
        $index = 0;

        $directory = $this->publicPath."/images/uploads/users";

        /** @var User $user */
        foreach ($io->progressIterate($queryBuilder->getQuery()->toIterable()) as $user) {
            $oldFilename = $user->getPicture();
            $newFilename = $this->extractUniqId($oldFilename);
            $oldPath = $directory."/".$user->getPicture();

            if (!file_exists($oldPath)) {
                continue;
            }

            $newPath = $directory."/".$newFilename;

            $user->setPicture($newFilename);
            rename($oldPath, $newPath);
            $io->comment(sprintf("%s => %s", $oldPath, $newPath));
            
            $oldPath = $directory."/200px/".$oldFilename;

            if (file_exists($oldPath)) {
                $newPath = $directory."/200px/".$newFilename;
                rename($oldPath, $newPath);
                $io->comment(sprintf("%s => %s", $oldPath, $newPath));
            }

            $index++;

            if (($index % self::BATCH) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }
        $this->entityManager->flush();
    }

    private function renameDriveFilenames(SymfonyStyle $io): void {
        $io->info('Renaming drive filenames');
        /**
         * @var DrivedFileRepository $repository
         */
        $repository = $this->entityManager->getRepository(DrivedFile::class);

        $queryBuilder = $repository->createQueryBuilder('d');
        
        $index = 0;

        $directory = $this->publicPath."/images/uploads/drive";

        /** @var DrivedFile $driveFile */
        foreach ($io->progressIterate($queryBuilder->getQuery()->toIterable()) as $driveFile) {
            $oldFilename = $driveFile->getFilename();
            $newFilename = $this->extractUniqId($driveFile->getFilename());
            $oldPath = $directory."/".$oldFilename;

            if (!file_exists($oldPath)) {
                continue;
            }

            $newPath = $directory."/".$newFilename;

            $driveFile->setFilename($newFilename);
            rename($oldPath, $newPath);
            $io->comment(sprintf("%s => %s", $oldPath, $newPath));
            
            $index++;

            if (($index % self::BATCH) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }
        $this->entityManager->flush();
    }

    private function renameArticleImages(SymfonyStyle $io, string $entityStringType, string $entityClass, string $directory): void {
        $io->info('Renaming '.$entityStringType.' filenames');
        /**
         * @var AbstractArticleRepository $abstractArticleRepository
         */
        $abstractArticleRepository = $this->entityManager->getRepository($entityClass);
        
        $queryBuilder = $abstractArticleRepository->createQueryBuilder('a');
        $queryBuilder->where($queryBuilder->expr()->neq('a.images', '\'[]\''));

        $index = 0;

        /**
         * @var AbstractArticle $article
         */
        foreach ($io->progressIterate($queryBuilder->getQuery()->toIterable()) as $article) {
            $oldFilenames = $article->getImages();
            $oldNewPathMap = [];
            $newFilenames = [];
            foreach ($oldFilenames as $oldFilename) {
                $oldPath = $directory."/".$oldFilename;

                if (!file_exists($oldPath)) {
                    continue;
                }
                
                $newFilename = $this->extractUniqId($oldFilename);
                $newPath = $directory."/".$newFilename;

                $newFilenames[] = $newFilename;
                $oldNewPathMap[$oldPath] = $newPath;

                $oldPath = $directory."/375px/".$oldFilename;

                if (file_exists($oldPath)) {
                    $newPath = $directory."/375px/".$newFilename;
                    $oldNewPathMap[$oldPath] = $newPath;
                }
            }
            

            $article->setImages($newFilenames);

            foreach ($oldNewPathMap as $oldPath => $newPath) {
                rename($oldPath, $newPath);
                $io->comment(sprintf("%s => %s", $oldPath, $newPath));
            }

            $index++;
            
            if (($index % self::BATCH) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }
        $this->entityManager->flush();
        
    }

    private function extractUniqId(string $fileName): string {
        $filenameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        return explode('_', $filenameWithoutExtension)[0].'.'.$extension;   
    }
}
