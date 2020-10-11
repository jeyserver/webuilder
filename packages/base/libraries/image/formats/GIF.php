<?php
namespace packages\base\Image;

use packages\base\IO\File;

class GIF extends GD {

	/**
	 * Save the image to a file.
	 * 
	 * @param packages\base\IO\File $file
	 * @param int $quality
	 * @return void
	 */
	public function saveToFile(File $file, int $quality = 75): void {
		imagegif($this->image, $file->getPath());
	}

	/**
	 * Get format of current image.
	 * 
	 * @return string
	 */
	public function getExtension(): string {
		return 'gif';
	}

	/**
	 * Read the image from constructor file.
	 * 
	 * @throws InvalidImageFileException if gd library was unable to load a gif image from the file.
	 * @return void
	 */
	protected function fromFile(): void {
		$this->image = imagecreatefromgif($this->file->getPath());
		if (!is_resource($this->image)) {
			throw new InvalidImageFileException($this->file);
		}
	}
}
