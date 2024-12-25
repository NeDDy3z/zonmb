<?php

namespace Helpers;

use Exception;
use GdImage;

/**
 * ImageHelper
 *
 * This helper class provides utility methods for image processing, including handling
 * image uploads, generating thumbnails, cropping/resizing images, and saving them
 * to specific paths. It is particularly useful for tasks such as creating profile
 * pictures or article thumbnails.
 *
 * @package Helpers
 */
class ImageHelper {

    /**
     * Create a `GdImage` object from an uploaded file.
     *
     * The method supports JPEG and PNG files. If the file type is unsupported,
     * an exception is thrown.
     *
     * @param array $image An associative array representing the uploaded file (from `$_FILES`).
     *
     * @return GdImage The generated `GdImage` object.
     *
     * @throws Exception If the image type is unsupported.
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
     * Resize an image while maintaining its aspect ratio to fit within the specified dimensions.
     *
     * This method scales the image down so it fits within the given width and height
     * while maintaining the original aspect ratio.
     *
     * @param GdImage $image The input `GdImage` object.
     * @param int $dstWidth The maximum width of the resized image.
     * @param int $dstHeight The maximum height of the resized image.
     *
     * @return GdImage The resized `GdImage` object.
     */
    public static function resize(GdImage $image, int $dstWidth, int $dstHeight): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Calculate the aspect ratio
        $aspectRatio = $width / $height;

        // Adjust dimensions to maintain aspect ratio
        if ($dstWidth / $dstHeight > $aspectRatio) {
            $newWidth = (int)($dstHeight * $aspectRatio);
            $newHeight = $dstHeight;
        } else {
            $newWidth = $dstWidth;
            $newHeight = (int)($dstWidth / $aspectRatio);
        }

        // Create a new true color image with the calculated dimensions
        $resizedImage = imagecreatetruecolor(width: $newWidth, height: $newHeight);

        // Resample the image to the new dimensions
        imagecopyresampled(
            dst_image: $resizedImage,
            src_image: $image,
            dst_x: 0,
            dst_y: 0,
            src_x: 0,
            src_y: 0,
            dst_width: $newWidth,
            dst_height: $newHeight,
            src_width: $width,
            src_height: $height,
        );

        // Free memory occupied by the original image
        imagedestroy($image);

        return $resizedImage;
    }

    /**
     * Check if an image file has been uploaded.
     *
     * This method verifies whether a given file was uploaded via HTTP POST.
     *
     * @param array<array<string|int>>|null $image The uploaded file (from `$_FILES`).
     *
     * @return bool Returns `true` if the file was uploaded; otherwise, `false`.
     *
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
     * Save a `GdImage` to a file.
     *
     * This method saves an image as a JPEG file with a specified quality and
     * ensures that resources are freed after saving.
     *
     * @param GdImage $image The `GdImage` object to save.
     * @param string $imagePath The file path where the image should be saved.
     *
     * @return void
     *
     * @throws Exception If the image cannot be saved.
     */
    public static function saveImage(GdImage $image, string $imagePath): void
    {
        $savedImage = imagejpeg(
            image: $image,
            file: $imagePath,
            quality: 80,
        );

        if (!$savedImage) {
            throw new Exception('imageSave');
        }

        imagedestroy($image);
    }

    /**
     * Normalize a nested array of uploaded files into a usable array format.
     *
     * This method converts the complex `$_FILES` array structure into an
     * easier-to-use representation.
     *
     * @param array $images The uploaded files array (from `$_FILES`).
     *
     * @return array<array<string, string>>|null A simplified array of file entries.
     */
    public static function getUsableImageArray(array $images): array|null
    {
        $results = [];
        $keys = array_keys($images);

        if (is_string($images['tmp_name'])) {
            $results[0] = $images;
        } else {
            $count = count($images['tmp_name']);

            foreach ($keys as $key) {
                for ($i = 0; $i < $count; $i++) {
                    $results[$i][$key] = $images[$key][$i];
                }
            }
        }

        if ($results[0]['tmp_name'] === "") {
            $results = null;
        }
        return $results;
    }

    /**
     * Process an uploaded image as a profile picture.
     *
     * Profile pictures are cropped to a 200x200 square if they exceed this size
     * and converted to a `GdImage` object.
     *
     * @param array $uploadedImage The uploaded file array (from `$_FILES`).
     *
     * @return GdImage The processed `GdImage` object.
     *
     * @throws Exception If the image upload fails.
     */
    public static function processProfilePicture(array $uploadedImage): GdImage
    {
        // Check if image was really uploaded
        if (!self::isImageUploaded($uploadedImage)) {
            throw new Exception('imageUploadError');
        }

        // Convert to GDImage object based on an image type
        $image = self::getGdImage($uploadedImage);

        // Get original dimensions
        $width = imagesx($image);
        $height = imagesy($image);

        // Crop if too large
        if ($width > 200 or $height > 200) {
            $image = self::resize($image, 200, 200);
        }

        return $image;
    }

    /**
     * Process an uploaded image for an article.
     *
     * This method converts an uploaded image to a `GdImage` object and
     * returns `null` if no image was uploaded.
     *
     * @param array $uploadedImage The uploaded file array (from `$_FILES`).
     *
     * @return GdImage|null The processed `GdImage` object or `null`.
     *
     * @throws Exception If the image type is unsupported.
     */
    public static function processArticleImage(array $uploadedImage): GdImage|null
    {
        // Check if image was really uploaded
        if (self::isImageUploaded($uploadedImage)) {
            return self::getGdImage($uploadedImage); // Convert to GDImage object based on an image type
        }
        else {
            return null;
        }
    }

    /**
     * Generate a thumbnail image.
     *
     * This method resizes an image to the desired dimensions and saves it to the
     * specified file path as a thumbnail.
     *
     * @param array|GdImage $image The input image array (from `$_FILES`) or `GdImage` object.
     * @param string $imagePath The file path where the thumbnail should be saved.
     * @param int $width The thumbnail width (default: 350px).
     * @param int $height The thumbnail height (default: 200px).
     *
     * @return void
     *
     * @throws Exception If the image type is unsupported or the save fails.
     */
    public static function generateThumbnail(array|GdImage $image, string $imagePath, int $width = 350, int $height = 200): void
    {
        if (is_array($image)) {
            $image = self::getGdImage($image);
        }

        self::saveImage(
            image: self::resize($image, $width, $height),
            imagePath: $imagePath,
        );
    }
}