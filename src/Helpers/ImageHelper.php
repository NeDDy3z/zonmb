<?php

namespace Helpers;

use Exception;
use GdImage;

class ImageHelper {

    /**
     * Get GdImage object from uploaded file
     * @param array $image
     * @return GdImage
     * @throws Exception
     */
    private static function getGdImage(array $image): GdImage
    {
        return match ($image['type']) {
            'image/jpeg' => imagecreatefromjpeg($image['tmp_name']),
            'image/png' => imagecreatefrompng($image['tmp_name']),
            default => throw new Exception('Unsupported image type'),
        };
    }

    /**
     * Crop image to desired dimensions
     * @param GdImage $image
     * @param int $dstWidth
     * @param int $dstHeight
     * @return GdImage
     */
    private static function resize(GdImage $image, int $dstWidth, int $dstHeight): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $x = (int)(($width - $dstWidth) / 2);
        $y = (int)(($height - $dstHeight) / 2);

        // Create the cropped image
        $croppedImage = imagecreatetruecolor(
            width: $dstWidth,
            height: $dstHeight,
        );


        imagecopy(
            dst_image: $croppedImage,
            src_image: $image,
            dst_x: 0,
            dst_y: 0,
            src_x: $x,
            src_y: $y,
            src_width: $dstWidth,
            src_height: $dstHeight,
        );

        imagedestroy($image);

        return $croppedImage;
    }

    /**
     * Check if file has been uploaded
     * @param array|null $image
     * @return bool
     * @throws Exception
     */
    public static function isImageUploaded(?array $image): bool
    {
        if (!isset($image) or !is_uploaded_file($image['tmp_name'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Save image
     * @param GdImage $image
     * @param string $imagePath
     * @return void
     * @throws Exception
     */
    public static function saveImage(GdImage $image, string $imagePath): void
    {
        $savedImage = imagejpeg(
            image: $image,
            file: $imagePath,
            quality: 80,
        );

        if (!$savedImage) {
            throw new Exception('Failed to save image');
        }

        imagedestroy($image);
    }

    /**
     * Convert complicated and unreasonable array of files to usable array of images
     * @param array $images
     * @return array<array<string, string>>
     */
    public static function getUsableImageArray(array $images): array
    {
        $results = [];
        $keys = array_keys($images);

        $count = count($images[$keys[0]]);

        for ($i = 0; $i < $count; $i++) {
            $item = [];
            foreach ($keys as $key) {
                $item[$key] = $images[$key][$i];
            }
            $results[] = $item;
        }

        return $results;
    }

    /**
     * Process image as profile picture - crop to 500x500
     * @param array $uploadedImage
     * @return GdImage
     * @throws Exception
     */
    public static function processProfilePicture(array $uploadedImage): GdImage
    {
        // Check if image was really uploaded
        if (!self::isImageUploaded($uploadedImage)) {
            throw new Exception('uploadError');
        }

        // Convert to GDImage object based on an image type
        $image = self::getGdImage($uploadedImage);

        // Get original dimensions
        $width = imagesx($image);
        $height = imagesy($image);

        // Crop if too large
        if ($width > 500 or $height > 500) {
            $image = self::resize($image, 500, 500);
        }

        return $image;
    }

    /**
     * Process image for article
     * @param array $uploadedImage
     * @return GdImage|null
     * @throws Exception
     */
    public static function processArticleImage(array $uploadedImage): GdImage|null
    {
        return self::getGdImage($uploadedImage);

        // Check if image was really uploaded
        if (self::isImageUploaded($uploadedImage)) {
            return self::getGdImage($uploadedImage); // Convert to GDImage object based on an image type
        }
        else {
            return null;
        }
    }
}