<?php

namespace Linkedcode\Slim\Service;

use Exception;
use Linkedcode\Slim\Settings;
use Psr\Http\Message\UploadedFileInterface;

class UploadService
{
    private Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    protected function createDirs(UploadedFileInterface $file, int $entityId)
    {
        $path = $this->settings->get('upload.path');
        $full = null;

        $type = $file->getClientMediaType();

        switch ($type) {
            case 'image/webp':
                $ext = 'webp';
                break;
            case 'image/jpeg':
                $ext = 'jpg';
                break;
            case 'image/gif':
                $ext = 'gif';
                break;
            case 'image/png':
                $ext = 'png';
                break;
            default:
                throw new Exception("Invalid type: {$type}");
        }

        do {
            $md5 = md5($entityId . time() . random_bytes(10));
            $dir = sprintf("%'.09d", $entityId);
            $dirs = str_split($dir, 3);

            $fulldir = $path . implode("/", $dirs) . "/";
            $full = $fulldir . substr($md5, 0, 8) . "." . $ext;
            $full = $this->getFilenameVersion($full, "orig");
            
            if (!file_exists($fulldir)) {
                mkdir($fulldir, 0777, true);
            }
        } while (file_exists($full));

        return $full;
    }

    public function upload(UploadedFileInterface $file, int $entityId): string|bool
    {
        try {
            $path = $this->settings->get('upload.path');
            $full = $this->createDirs($file, $entityId);

            $file->moveTo($full);

            if ($this->checkMinSize($full) === false) {
                return false;
            }

            $full = $this->createWebpVersion($full);

            $this->checkMaxSize($full);

            $this->createVersions($full);

            $relPath = str_replace($path, "", $full);
            return $relPath;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function createVersions(string $filename)
    {
        $versions = $this->settings->get('upload.versions');

        foreach ($versions as $version) {
            $ver = explode("x", $version);
            $w = (int) $ver[0];
            $h = (int) $ver[1];
            $filenameVersion = $this->getFilenameVersion($filename, $version);
            $this->createVersion($filename, $filenameVersion, $w, $h);
        }
    }

    public function getFilenameVersion(string $file, string $version)
    {
        $original = ".orig.";

        if (stripos($file, $original) !== false) {
            $filename = str_replace($original, ".{$version}.", $file);
            $pathinfo = pathinfo($filename);
            $filename = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . '.webp';
        } else {
            $pathinfo = pathinfo($file);
            $filename = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . ".{$version}." . $pathinfo['extension'];
        }
        
        return $filename;
    }

    private function isAlpha(string $filename): bool
    {
        $info = getimagesize($filename);

        switch ($info['mime']) {
            case 'image/gif':
            case 'image/png':
                return true;
        }

        return false;
    }

    protected function createVersion(string $source, string $target, int $thumbWidth, int $thumbHeight): bool
    {
        $image = $this->createImageFromFile($source);

        $width = imagesx($image);
        $height = imagesy($image);

        $originalAspect = $width / $height;
        $thumbAspect = $thumbWidth / $thumbHeight;

        if ($originalAspect >= $thumbAspect) {
            // If image is wider than thumbnail (in aspect ratio sense)
            $newHeight = $thumbHeight;
            $newWidth = $width / ($height / $thumbHeight);
        } else {
            // If the thumbnail is wider than the image
            $newWidth = $thumbWidth;
            $newHeight = $height / ($width / $thumbWidth);
        }

        if ($this->isAlpha($source)) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // Resize and crop
        imagecopyresampled($thumb,
            $image,
            0 - ($newWidth - $thumbWidth) / 2, // Center the image horizontally
            0 - ($newHeight - $thumbHeight) / 2, // Center the image vertically
            0, 0,
            $newWidth, $newHeight,
            $width, $height
        );

        imagewebp($thumb, $target, 90);

        return true;
    }

    private function checkMaxSize(string $filename)
    {
        $image = $this->createImageFromFile($filename);

        $width = imagesx($image);
        $height = imagesy($image);

        $maxWidth = $this->settings->get('upload.maxWidth');
        $maxHeight = $this->settings->get('upload.maxHeight');

        if ($width > $maxWidth || $height > $maxHeight) {
            $this->createVersion($filename, $filename, $maxWidth, $maxHeight);
        }
    }

    private function checkMinSize(string $filename): bool
    {
        $image = $this->createImageFromFile($filename);

        $width = imagesx($image);
        $height = imagesy($image);

        $minWidth = $this->settings->get('upload.minWidth');
        $minHeight = $this->settings->get('upload.minHeight');

        if ($width < $minWidth || $height < $minHeight) {
            return false;
        }

        return true;
    }

    private function isWebp(string $filename): bool
    {
        $info = getimagesize($filename);
        return ('image/webp' === $info['mime']);
    }

    private function createWebpVersion(string $filename): string
    {
        if ($this->isWebp($filename)) {
            return $filename;
        }

        $image = $this->createImageFromFile($filename);

        $width = imagesx($image);
        $height = imagesy($image);

        $pathinfo = pathinfo($filename);
        $newname = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.webp';

        $this->createVersion($filename, $newname, $width, $height);

        unlink($filename);
        return $newname;
    }

    private function createImageFromFile(string $filename)
    {
        $info = getimagesize($filename);

        switch ($info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filename);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filename);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($filename);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($filename);
                break;
            default:
                throw new Exception("Imagen '{$info['mime']}' no permitida");
        }

        return $image;
    }
}