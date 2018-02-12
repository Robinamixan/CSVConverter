<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class File
{
    /**
    * @Assert\NotBlank(message="Please, upload the product brochure as a csv file.")
    * @Assert\File(mimeTypes={ "text/plain" })
    */
    private $file;

    private $flagTestMode;

    public function getFlagTestMode()
    {
        return $this->flagTestMode;
    }

    public function setFlagTestMode(bool $flagTestMode)
    {
        $this->flagTestMode = $flagTestMode;
    }

    public function getFile(): ?\SplFileObject
    {
        return $this->getSavedFile($this->file);
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    private function getSavedFile(?\SplFileInfo $uploadedFile): ?\SplFileObject
    {
        if ($uploadedFile) {
            $filePath = $uploadedFile->getPath();
            $fileName = $uploadedFile->getClientOriginalName();
            $uploadedFile->move($filePath, $fileName);
            return new \SplFileObject($filePath . '/' . $fileName, 'r');
        }
        return null;
    }
}
