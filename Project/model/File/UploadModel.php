<?php

namespace model;

class UploadModel {

    public function __construct() {

    }

    public function UploadImage($fileObject, $toDirectory) {
        $acceptedMimeTypes = array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        );

        return $this->doUploadFile($fileObject, $toDirectory, $acceptedMimeTypes, \Settings::IMAGE_MAX_SIZE);
    }

    private function doUploadFile($fileObject, $toDirectory, $acceptedMimeTypes, $maxFileSize = 0) {
        $toDirectory = rtrim($toDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR; //make sure folder ends with "/"
        assert(file_exists($toDirectory));

        try {

            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (
                !isset($fileObject['error']) ||
                is_array($fileObject['error'])
            ) {
                throw new \Exception('Invalid parameters.');
            }

            // Check $fileObject['error'] value.
            switch ($fileObject['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new \Exception('No file sent.');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new \Exception('Exceeded filesize limit.');
                default:
                    throw new \Exception('Unknown errors.');
            }

            // You should also check filesize here.
            if ($maxFileSize <= 0 || $fileObject['size'] > $maxFileSize) {
                throw new \Exception('Exceeded filesize limit.');
            }

            // DO NOT TRUST $fileObject['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                    $finfo->file($fileObject['tmp_name']),
                    $acceptedMimeTypes,
                    true)) {

                throw new \Exception('Invalid file format.');
            }

            $filename = sprintf($toDirectory . '%s.%s',
                sha1_file($fileObject['tmp_name']),
                $ext
            );

            // You should name it uniquely.
            // DO NOT USE $fileObject['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.
            if (!move_uploaded_file(
                $fileObject['tmp_name'],
                $filename
            )) {
                throw new \Exception('Failed to move uploaded file.');
            }

            //echo 'File is uploaded successfully.';
            return $filename;

        } catch (\Exception $e) {

            throw $e;
            //echo $e->getMessage();

        }
    }


    public function CreateImageThumbnail($directory, $filename, $thumb_directory, $thumb_width) {
        $thumb_directory = rtrim($thumb_directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR; //make sure folder ends with "/"
        assert(file_exists($thumb_directory));

        $im = null;
        if(preg_match('/[.](jpg)$/', $filename)) {
            $im = imagecreatefromjpeg($directory . $filename);
        } else if (preg_match('/[.](gif)$/', $filename)) {
            $im = imagecreatefromgif($directory . $filename);
        } else if (preg_match('/[.](png)$/', $filename)) {
            $im = imagecreatefrompng($directory . $filename);
        } else {
            throw new \Exception("Image type not supported!");
        }

        $ox = imagesx($im);
        $oy = imagesy($im);

        $nx = $thumb_width;
        $ny = floor($oy * ($thumb_width / $ox));

        $nm = imagecreatetruecolor($nx, $ny);

        //create a thumbnail image object with specified dimensions
        if (imagecopyresized($nm, $im, 0,0,0,0,$nx,$ny,$ox,$oy)) {

            //save thumbnail image to file
            return imagejpeg($nm, $thumb_directory . $filename);
        }

        return false;
    }


}
