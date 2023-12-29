<?php

namespace App\Service;

class ImageManager
{
    private $current_path;

    const SMALL_SIZE = 220;
    const NORMAL_SIZE = 455;

    public function __construct()
    {
        $this->current_path = getcwd();
    }

    public function resizeImages($source, $destination)
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

    public function resizeImage(string $image_path, int $desired_size = self::NORMAL_SIZE)
    {
        $image = getimagesize($image_path)["mime"] == 'image/png' 
            ? imagecreatefrompng($image_path) 
            : imagecreatefromjpeg($image_path);
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
}
