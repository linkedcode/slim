<?php

namespace Linkedcode\Slim\Service;

use Exception;
use Linkedcode\Slim\Settings;
use Psr\Http\Message\UploadedFileInterface;

class UploadService
{
    //protected ListingRepository $entityRepo;
    
    //protected ListingImageRepository $imageRepo;

    private Settings $settings;

    public function __construct(Settings $settings)
    {
        //$this->entityRepo = $listingRepo;
        //$this->imageRepo = $imageRepo;
        $this->settings = $settings;
    }

    protected function createDirs(UploadedFileInterface $file, int $entityId)
    {
        $path = $this->settings->get('uploadPath');
        $full = null;

        do {
            $md5 = md5($entityId . time());
            $dir = sprintf("%'.09d", $entityId);
            $dirs = str_split($dir, 3);
            $type = $file->getClientMediaType();
            
            switch ($type) {
                case 'image/jpeg':
                    $ext = 'jpg';
                    break;
                default:
                    throw new Exception("Invalid type: {$type}");
            }

            $fulldir = $path . implode("/", $dirs) . "/";
            $full = $fulldir . substr($md5, 0, 8) . "." . $ext;
            $full = $this->getFilenameVersion($full, "orig");
            
            if (!file_exists($fulldir)) {
                mkdir($fulldir, 0777, true);
            }
        } while (file_exists($full));

        return $full;
    }

    /**
     * @return string Path relativo del archivo original.
     */
    public function upload(UploadedFileInterface $file, int $entityId): string
    {
        $path = $this->settings->get('uploadPath');
        $full = $this->createDirs($file, $entityId);

        try {
            $file->moveTo($full);
            $relPath = str_replace($path, "", $full);

            $this->createVersion($full, 300, 225);
            $this->createVersion($full, 90, 90);
        } catch (Exception $e) {

        }

        return $relPath;
    }

    public function getFilenameVersion($file, $version)
    {
        $original = ".orig.";

        if (stripos($file, $original) !== false) {
            $filename = str_replace($original, ".{$version}.", $file);
        } else {
            $pathinfo = pathinfo($file);
            $filename = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . ".{$version}." . $pathinfo['extension'];
        }
        
        return $filename;
    }

    protected function createVersion(string $file, int $thumb_width, int $thumb_height)
    {
        $image = imagecreatefromjpeg($file);
        $filename = $this->getFilenameVersion($file, "{$thumb_width}x{$thumb_height}");

        $width = imagesx($image);
        $height = imagesy($image);

        $original_aspect = $width / $height;
        $thumb_aspect = $thumb_width / $thumb_height;

        if ( $original_aspect >= $thumb_aspect ) {
            // If image is wider than thumbnail (in aspect ratio sense)
            $new_height = $thumb_height;
            $new_width = $width / ($height / $thumb_height);
        } else {
            // If the thumbnail is wider than the image
            $new_width = $thumb_width;
            $new_height = $height / ($width / $thumb_width);
        }

        $thumb = imagecreatetruecolor( $thumb_width, $thumb_height );

        // Resize and crop
        imagecopyresampled($thumb,
            $image,
            0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
            0 - ($new_height - $thumb_height) / 2, // Center the image vertically
            0, 0,
            $new_width, $new_height,
            $width, $height
        );
        
        imagejpeg($thumb, $filename, 80);

        return $filename;
    }
}