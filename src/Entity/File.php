<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class File
{
    /**
     * @Assert\NotBlank(message="Please, upload the product brochure as a csv file.")
     * @Assert\File(mimeTypes={ "text/plain" })
     */
    private $file;

    private $flagTestMode;

    public function getFlagTestMode(): ?bool
    {
        return $this->flagTestMode;
    }

    public function setFlagTestMode(bool $flagTestMode): void
    {
        $this->flagTestMode = $flagTestMode;
    }

    public function getFile(): ?\SplFileObject
    {
        return $this->getSavedFile($this->file);
    }

    public function setFile($file): void
    {
        $this->file = $file;
    }

    private function getSavedFile(?\SplFileInfo $uploadedFile): ?\SplFileObject
    {
        if ($uploadedFile) {
            $filePath = $uploadedFile->getPath();
            $fileName = $uploadedFile->getClientOriginalName();
            $uploadedFile->move($filePath, $fileName);

            return new \SplFileObject($filePath.'/'.$fileName, 'r');
        }

        return null;
    }
}
