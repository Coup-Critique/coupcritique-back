<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\HttpFoundation\FileBag;

class ImageArticleManager
{
    const IMAGE_SIZE        = 900;
    const TEASER_IMAGE_SIZE = 375;

    protected FileManager $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function setImagesToEntity(object $entity, FileBag $files, string $storageFolderName): void
    {
        $images = $entity->getImages();

        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $fileName      = $this->fileManager->upload($file, 'images/' . $storageFolderName);
            $filePath      = "images/$storageFolderName/$fileName";
            $fileTeaserPath = $this->fileManager->copy(
                $filePath,
                'images/' . $storageFolderName . '/' . self::TEASER_IMAGE_SIZE . 'px'
            );
            // resize image
            $this->fileManager->resize($filePath, self::IMAGE_SIZE);
            $this->fileManager->resize($fileTeaserPath, self::TEASER_IMAGE_SIZE);

            $images[] = $fileName;
        }

        $entity->setImages($images);
    }

    public function upload(FileBag $files, string $storageFolderName = 'drive', $size = self::IMAGE_SIZE): array
    {
        $images = [];
        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $fileName      = $this->fileManager->upload($file, 'images/' . $storageFolderName);
            $filePath      = "images/$storageFolderName/$fileName";
            // resize image
            $this->fileManager->resize($filePath, $size);

            $images[] = $fileName;
        }

        return $images;
    }

    /**
     * @param $entity
     * @param array $imagesToCheck
     * @return void
     * leave empty $imagesToCheck for remove all
     */
    public function removeImagesFromEntity($entity, $storageFolderName, array $imagesToCheck = [])
    {
        $imagesToRemove = count($imagesToCheck)
            ? array_diff($imagesToCheck, $entity->getImages())
            : $entity->getImages();

        foreach ($imagesToRemove as $image) {
            $this->fileManager->remove("images/$storageFolderName/$image");
            $this->fileManager->remove("images/$storageFolderName/" . self::TEASER_IMAGE_SIZE . "px/$image");
        }
    }
}
