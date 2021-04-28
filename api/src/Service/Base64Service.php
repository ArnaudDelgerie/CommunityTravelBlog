<?php

namespace App\Service;

class Base64Service
{

    private $filePrefix = "img_";

    private $base64;

    private $extension;

    /**
     * @param string $base64
     */
    public function checkData($base64): bool
    {
        if ( !base64_decode($base64)){
            return false;
        }

        $data = explode(',', $base64);
        $this->base64 = $data[1];

        if (strpos($data[0], 'png') == true) {
            $this->extension = '.png';
            return true;
        } elseif (strpos($data[0], 'jpeg') == true) {
            $this->extension = '.jpeg';
            return true;
        } elseif (strpos($data[0], 'jpg') == true) {
            $this->extension = '.jpg';
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $basePath
     */
    public function convertToFile(string $basePath): string
    {
        $fileName = $this->generateFileName() . $this->extension;
        $filePath = $basePath . "/" . $fileName;

        $file = fopen($filePath, 'wb');
        fwrite($file, base64_decode($this->base64));
        fclose($file);

        return $fileName ;
    }

    /**
     * @return string
     */
    private function generateFileName(): string
    {
        return uniqid($this->filePrefix, true);
    }

}