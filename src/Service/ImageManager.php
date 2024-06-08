<?php

namespace App\Service;

use GdImage;

class ImageManager
{
    private $current_path;

    final public const SMALL_SIZE = 220;
    final public const NORMAL_SIZE = 455;

    private const AVIF_MIME_TYPE = 'image/avif';
    private const GIF_MIME_TYPE = 'image/gif';
    private const JPEG_MIME_TYPE = 'image/jpeg';
    private const PNG_MIME_TYPE = 'image/png';
    private const WEBP_MIME_TYPE = 'image/webp';
    

    public function __construct()
    {
        $this->current_path = getcwd();
    }

    public function resizeImages($source, $destination): void
    {
        $source_path = "{$this->current_path}$source";
        $destination_path = "{$this->current_path}$destination";

        if (is_dir($source_path)) {

            // if result directory doesnt exist it gets created
            if (!file_exists("$destination_path/" . self::SMALL_SIZE . "px")) {
                mkdir("$destination_path/" . self::SMALL_SIZE . "px", 0775, true);
            }

            if ($directory = opendir($source_path)) {
                // for each file in the directory
                while (($file = readdir($directory)) !== false) {
                    if (
                        !is_dir("$source_path/$file") &&
                        $file != "." &&
                        $file != ".." &&
                        pathinfo("$source_path/$file")["extension"] == "png"
                    ) {
                        // file already existing at this size are not treated
                        if (!file_exists("$destination_path/" . self::SMALL_SIZE . "px/$file")) {
                            // resize small image
                            $resized_image = $this->resizeImage("$source_path/$file", self::SMALL_SIZE);

                            // save small image 
                            imagesavealpha($resized_image, True);
                            imagepng($resized_image, "$destination_path/" . self::SMALL_SIZE . "px/$file");
                        }
                        // resize small image
                        $resized_image = $this->resizeImage("$source_path/$file");

                        // save normal image
                        imagesavealpha($resized_image, True);
                        imagepng($resized_image, "$destination_path/$file");
                    }
                }
                closedir($directory);
            }
        } else if (is_file($source_path)) {
            $image_name = basename($source_path);
            // $directory_path = str_replace($image_name, "", $source_path);

            // if result directory doesnt exist it gets created
            if (!file_exists("$destination_path" . self::SMALL_SIZE . "px")) {
                mkdir("$destination_path" . self::SMALL_SIZE . "px", 0775, true);
            }
            // resize image to small
            $resized_image = $this->resizeImage($source_path, self::SMALL_SIZE);

            // save image to small
            imagesavealpha($resized_image, True);
            imagepng($resized_image, "$destination_path" . self::SMALL_SIZE . "px/{$image_name}");

            // resize image to small
            $resized_image = $this->resizeImage($source_path);

            // save image to small
            imagesavealpha($resized_image, True);
            imagepng($resized_image, "$destination_path/$image_name");
        }
    }

    public function resizeImage(string $image_path, int $desired_size = self::NORMAL_SIZE): \GdImage|false
    {
        $image = self::createImageFromPath($image_path);
        
        $image_x = imageSX($image);
        $image_y = imageSY($image);

        if ($image_x > $image_y) {
            $new_x = $desired_size;
            $new_y = $image_y / ($image_x / $desired_size);
        } else {
            $new_y = $desired_size;
            $new_x = $image_x / ($image_y / $desired_size);
        }

        return imagescale($image, $new_x, $new_y);
    }

    public function createImageFromPath(string $image_path): GdImage|null {
        $mimetype = getimagesize($image_path)["mime"];
        $image = null;

        switch($mimetype) {
            case self::AVIF_MIME_TYPE:
                $image = imageCreateFromAVIF($image_path);
                break;

            case self::GIF_MIME_TYPE:
                $image = imageCreateFromGIF($image_path);
                break;

            case self::JPEG_MIME_TYPE:
                $image = imageCreateFromJPEG($image_path);
                break;

            case self::PNG_MIME_TYPE:
                $image = imageCreateFromPNG($image_path);
                break;
            
            case self::WEBP_MIME_TYPE:
                $image = imageCreateFromWEBP($image_path);
                break;
        }
        
        return $image;
    }

    public function saveImage(GdImage $image, string $path) {
        $mimetype = getimagesize($path)["mime"];

        switch($mimetype) {
            case self::AVIF_MIME_TYPE:
                imageAvif($image, $path);
                return;

            case self::GIF_MIME_TYPE:
                imageGIF($image, $path);
                return;

            case self::JPEG_MIME_TYPE:
                imageJPEG($image, $path);
                return;

            case self::PNG_MIME_TYPE:
                imagePNG($image, $path);
                return;
            
            case self::WEBP_MIME_TYPE:
                imageWEBP($image, $path);
                return;
        }
    }

    public function cropCenter(GdImage $image): GdImage {

        // Formula from https://stackoverflow.com/a/49851547
        $calculatePixelsForAlign = fn($imageSize, $cropSize) => [ max(0, floor(($imageSize / 2) - ($cropSize / 2))), min($cropSize, $imageSize) ];

        $width = imageSX($image);
        $height = imageSY($image);
        // Determine the horizontal coordinate of the center
        $horizontalAlignPixels = $calculatePixelsForAlign($width, min($width, $height));
        // Determine the vertical coordinate of the center
        $verticalAlignPixels = $calculatePixelsForAlign($height, min($width, $height));
        $image = imageCrop($image, [
            'x' => $horizontalAlignPixels[0],
            'y' => $verticalAlignPixels[0],
            // Make sure that the image produced has a square shape
            'width' => min($width, $height),
            'height' => min($width, $height),
        ]);
        
        return $image;
    }
}
