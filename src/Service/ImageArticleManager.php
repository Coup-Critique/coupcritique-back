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

    public function setImagesToEntity(object $entity, FileBag $files, string $storageFolderName): array
    {
        $errors = [];
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
            try {
                $this->fileManager->resize($filePath, self::IMAGE_SIZE);
                $this->fileManager->resize($fileTeaserPath, self::TEASER_IMAGE_SIZE);
                $images[] = $fileName;
            } catch (\Exception) {
                $errors[] = $fileName;
            }
        }

        $entity->setImages($images);
        return $errors;
    }

    public function upload(FileBag $files, string $storageFolderName = 'drive', int $size = self::IMAGE_SIZE): array
    {
        $images = [];
        $errors = [];
        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $fileName      = $this->fileManager->upload($file, 'images/upload/' . $storageFolderName);
            $filePath      = "images/uploads/$storageFolderName/$fileName";
            // resize image
            try {
                $this->fileManager->resize($filePath, $size);
                $images[] = $fileName;
            } catch (\Exception) {
                $errors[] = $fileName;
            }
        }

        return [$images, $errors];
    }

    /**
     * leave empty $imagesToCheck for remove all
     */
    public function removeImagesFromEntity($entity, string $storageFolderName, ?array $imagesToCheck = []): void
    {
        $imagesToRemove = $imagesToCheck && count($imagesToCheck)
            ? array_diff($imagesToCheck, $entity->getImages())
            : [];

        foreach ($imagesToRemove as $image) {
            $this->fileManager->remove("images/uploads/$storageFolderName/$image");
            $this->fileManager->remove("images/uploads/$storageFolderName/" . self::TEASER_IMAGE_SIZE . "px/$image");
        }
    }
}
