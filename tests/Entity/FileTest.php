<?php

namespace App\Tests\Entity;

use App\Entity\File;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileTest extends TestCase
{
    public function testLoadFile()
    {
        $file = new File();
        $testFilePath = 'tests/Entity/testFile1';

        file_put_contents($testFilePath, 'test', FILE_APPEND);

        $uploadedFile = new UploadedFile(
            $testFilePath,
            'testFile2.txt',
            null,
            null,
            null,
            true
        );

        $file->setFile($uploadedFile);
        $savedFile = $file->getFile();

        unlink($savedFile->getPathname());

        $this->assertEquals('testFile2.txt', $savedFile->getFilename());
    }
}
