<?php

namespace App\Plugins;

use Phalcon\Mvc\User\Plugin;

/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class FilePlugin extends Plugin
{

    public function checkExtension($file)
    {
        $extensions = ["image/jpeg", "image/jpg", "image/png"];
        if (!in_array($file->getRealType(), $extensions)) {
            return FALSE;
        }
        return TRUE;
    }


    public function getImage($file, $file_name)
    {
        $file_array = explode(".", $file->getName());
        $file_extension = end($file_array);
        return $file_name . "." . $file_extension;
    }

    public function uploadImage($file, $image)
    {
        if (!$file->moveTo(IMG_PATH . $image)) {
            return FALSE;
        }
        return TRUE;
    }


    public function deleteImage($image)
    {
        $image_abs = IMG_PATH . $image;

        if (file_exists($image_abs) && is_file($image_abs)) {
            unlink($image_abs);
        } else {
            return FALSE;
        }

        return TRUE;
    }


    public function renameImage($image, $image_name)
    {
        $image_array = explode(".", $image);
        $image_extension = end($image_array);
        $new_image = $image_name . "." . $image_extension;
        $image_abs = IMG_PATH . $image;
        $new_image_abs = IMG_PATH . $new_image;

        if (file_exists($image_abs) && is_file($image_abs)) {
            if (!rename($image_abs, $new_image_abs)) {
                return FALSE;
            }
        } else {
            return FALSE;
        }

        return $new_image;
    }
}
}
