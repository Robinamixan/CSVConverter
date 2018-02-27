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
        $reader = new FileReader();
        $testFilePath = 'tests/Service/testFile1.csv';

        file_put_contents($testFilePath, "a,b,c\n", FILE_APPEND);
        file_put_contents($testFilePath, "d,e,f\n", FILE_APPEND);
        $testAssertArray = ['a' => 'd', 'b' => 'e', 'c' => 'f'];

        $testFile = new \SplFileObject($testFilePath, 'r');

        $reader->setFileForRead($testFile);
        $testResultArray = $reader->getNextItem();

        unlink($testFilePath);
        $this->assertEquals($testAssertArray,$testResultArray);
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
