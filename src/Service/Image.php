<?php

namespace Linkedcode\Slim\Service;

use Exception;
use GdImage;
use SplFileInfo;

class Image extends SplFileInfo
{
    private GdImage $image;

    private const MIME_IMAGE_JPG = 'image/jpeg';
    private const MIME_IMAGE_PNG = 'image/png';
    private const MIME_IMAGE_WEBP = 'image/webp';
    private const MIME_IMAGE_GIF = 'image/gif';

    public function __construct(string $filename)
    {
        parent::__construct($filename);
        $this->image = $this->createImageFromFile($filename);
    }

    private function createImageFromFile(string $filename): GdImage
    {
        $info = getimagesize($filename);

        switch ($info['mime']) {
            case self::MIME_IMAGE_JPG:
                return imagecreatefromjpeg($filename);
            case self::MIME_IMAGE_PNG:
                return imagecreatefrompng($filename);
                break;
            case self::MIME_IMAGE_WEBP:
                return imagecreatefromwebp($filename);
            case self::MIME_IMAGE_GIF:
                return imagecreatefromgif($filename);
            default:
                throw new Exception("Tipo MIME '{$info['mime']}' no permitido");
        }

        return $image;
    }
}