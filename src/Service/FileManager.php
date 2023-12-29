<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class FileManager
{
	protected string $publicPath;

	protected ImageManager $imageManager;

	public function __construct(string $projectDir, ImageManager $imageManager)
	{
		$this->publicPath = "$projectDir/public";
		$this->imageManager = $imageManager;
	}

	public function upload(UploadedFile $file, string $dirName): string
	{
		$fullDirName = "{$this->publicPath}/$dirName/";
		$this->createDirIfNotExists($fullDirName);
		$fileName = uniqid() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
		$file->move($fullDirName, $fileName);
		return $fileName;
	}

	public function remove($filePath)
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

	/**
	 * @param string $filePath 
	 * @param int $size 
	 * @throw Exception
	 * @return void 
	 */
	public function resize(string $filePath, int $size)
	{
		$fullPath = "{$this->publicPath}/$filePath";
		$resized_image = $this->imageManager->resizeImage($fullPath, $size);
		imagesavealpha($resized_image, True);
		if (getimagesize($fullPath)["mime"] == 'image/png') {
			imagepng($resized_image, $fullPath);
		} else {
			imagejpeg($resized_image, $fullPath);
		}
	}

	private function createDirIfNotExists($dirName)
	{
		if (!is_dir($dirName)) {
			mkdir($dirName, 0777, true);
		}
	}
}
