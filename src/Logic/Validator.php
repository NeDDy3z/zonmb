<?php

namespace Logic;

class Validator
{
    /**
     * @param $image
     * @return bool
     * @throws IncorrectInputException
     */
    public function validateImage($image, array $conditions = ['size', 'type', 'dimensions']): bool
    {
        $error = null;

        // Image havent been uploaded - skip validation
        if ($image['size'] === 0) {
            return true;
        }

        // Error in uploading
        if ($image['error'] !== UPLOAD_ERR_OK) {
            $error .= 'imageUploadError-';
        }
        // Size
        if ($image['size'] > 1000000) {
            $error .= 'imageSize-';
        }
        // Type
        if (!in_array($image['type'], ['image/png', 'image/jpg', 'image/jpeg'])) {
            $error .= 'imageFormat-';
        }
        // Dimensions
        list($width, $height) = getimagesize($image['tmp_name']);
        if ($width > 500 || $width !== $height) {
            $error .= 'imageDimensions-';
        }

        if ($error) {
            throw new IncorrectInputException($error);
        } else {
            return true;
        }
    }
}
