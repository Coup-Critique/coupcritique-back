<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

class ImageArticleManager
{
    final public const IMAGE_SIZE        = 900;
    final public const TEASER_IMAGE_SIZE = 375;

    public function __construct(protected FileManager $fileManager)
    {
    }

    public function setImagesToEntity(object $entity, FileBag $files, string $storageFolderName): void
    {
        $images = $entity->getImages();

        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $fileName      = $this->fileManager->upload($file, 'images/uploads/' . $storageFolderName);
            $filePath      = "images/uploads/$storageFolderName/$fileName";
            $fileTeaserPath = $this->fileManager->copy(
                $filePath,
                'images/uploads/' . $storageFolderName . '/' . self::TEASER_IMAGE_SIZE . 'px'
            );
            // resize image
            $this->fileManager->resize($filePath, self::IMAGE_SIZE);
            $this->fileManager->resize($fileTeaserPath, self::TEASER_IMAGE_SIZE);

            $images[] = $fileName;
        }

        $entity->setImages($images);
    }

    public function upload(FileBag $files, string $storageFolderName = 'drive', int $size = self::IMAGE_SIZE): array
    {
        $images = [];
        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $fileName      = $this->fileManager->upload($file, 'images/upload/' . $storageFolderName);
            $filePath      = "images/uploads/$storageFolderName/$fileName";
            // resize image
            $this->fileManager->resize($filePath, $size);

            $images[] = $fileName;
        }

        return $images;
    }

    /**
     * leave empty $imagesToCheck for remove all
     */
    public function removeImagesFromEntity($entity, string $storageFolderName, array $imagesToCheck = []): void
    {
        $imagesToRemove = count($imagesToCheck)
            ? array_diff($imagesToCheck, $entity->getImages())
            : $entity->getImages();

        foreach ($imagesToRemove as $image) {
            $this->fileManager->remove("images/uploads/$storageFolderName/$image");
            $this->fileManager->remove("images/uploads/$storageFolderName/" . self::TEASER_IMAGE_SIZE . "px/$image");
        }
    }
}
