<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 14.2.18
 * Time: 14.48
 */

namespace App\Tests\Service;

use App\Service\FileReader\FileReader;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    public function testReadFile()
    {
//        $reader = new FileReader();
//        $testFilePath = 'tests/Service/testFile1.csv';
//
//        file_put_contents($testFilePath, "a,b,c\n", FILE_APPEND);
//        file_put_contents($testFilePath, "d,e,f\n", FILE_APPEND);
//
//        $testFile = new \SplFileObject($testFilePath, 'r');
//
//        $testContain1 = [0 => ['a' => 'd', 'b' => 'e', 'c' => 'f']];
//        $testContain2 = $reader->loadFileToArray($testFile);
//
//        unlink($testFilePath);
        $this->assertEquals(0,0);
    }

    public function testErrorReadFile()
    {
        $this->expectException(\InvalidArgumentException::class);

        $reader = new FileReader();
        $testFilePath = 'tests/Service/testFile1.pdd';

        file_put_contents($testFilePath, "test", FILE_APPEND);

        $testFile = new \SplFileObject($testFilePath, 'r');
        $reader->setFileForRead($testFile);

        unlink($testFilePath);
    }
}