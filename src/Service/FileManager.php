<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager
{
	protected string $publicPath;

	public function __construct(string $publicPath, protected ImageManager $imageManager)
	{
		$this->publicPath = $publicPath;
	}

	public function upload(UploadedFile $file, string $dirName): string
	{
		$fullDirName = "{$this->publicPath}/$dirName/";
		$this->createDirIfNotExists($fullDirName);
		$file->getClientOriginalName();
		$fileName = uniqid() . '.' . pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
		$file->move($fullDirName, $fileName);
		return $fileName;
	}

	public function remove($filePath): void
	{
		$fullPath = "{$this->publicPath}/$filePath";
		if (file_exists($fullPath)) {
			unlink($fullPath);
		}
	}

	public function copy(string $filePath, string $dirDest): string
	{
		$splitPath = explode('/', $filePath);
		$fileName  = array_pop($splitPath);
		$this->createDirIfNotExists("{$this->publicPath}/$dirDest/");
		copy("{$this->publicPath}/$filePath", "{$this->publicPath}/$dirDest/$fileName");
		return "$dirDest/$fileName";
	}

	public function resize(string $filePath, int $size): void
	{
		$fullPath = "{$this->publicPath}/$filePath";
		$resized_image = $this->imageManager->resizeImage($fullPath, $size);
		imageSaveAlpha($resized_image, True);
		$this->imageManager->saveImage($resized_image, $fullPath);
		imageDestroy($resized_image);
	}

	public function imageCropCenter(string $filePath): void {
		$fullPath = "{$this->publicPath}/$filePath";
		$image = $this->imageManager->cropCenter($this->imageManager->createImageFromPath($fullPath));
		$this->imageManager->saveImage($image, $fullPath);
		imageDestroy($image);
	}

	private function createDirIfNotExists($dirName): void
	{
		if (!is_dir($dirName)) {
			mkdir($dirName, 0777, true);
		}
	}
}
