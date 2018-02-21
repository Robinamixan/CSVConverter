<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 14.2.18
 * Time: 14.48
 */

namespace App\Tests\Service;


use App\Service\Reader\Reader;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    public function testReadFile()
    {
        $reader = new Reader();
        $testFilePath = 'tests/Service/testFile1.csv';

        file_put_contents($testFilePath, "a,b,c\n", FILE_APPEND);
        file_put_contents($testFilePath, "d,e,f\n", FILE_APPEND);

        $testFile = new \SplFileObject($testFilePath, 'r');

        $testContain1 = [['a', 'b', 'c'], ['d', 'e', 'f']];
        $testContain2 = $reader->loadFile($testFile);

        unlink($testFilePath);
        $this->assertEquals($testContain1, $testContain2);
    }

    public function testErrorReadFile()
    {
        $reader = new Reader();
        $testFilePath = 'tests/Service/testFile1.pdd';

        file_put_contents($testFilePath, "test", FILE_APPEND);

        $testFile = new \SplFileObject($testFilePath, 'r');
        $reader->loadFile($testFile);

        $testError1 = 'Unsupported type of input file';
        $testError2 = $reader->getFailReport();

        unlink($testFilePath);
        $this->assertEquals($testError1, $testError2);
    }
}