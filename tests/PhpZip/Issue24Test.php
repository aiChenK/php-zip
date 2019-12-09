<?php

namespace PhpZip;

use PhpZip\Exception\ZipException;

/**
 * @internal
 *
 * @small
 */
class Issue24Test extends ZipTestCase
{
    /**
     * This method is called before the first test of this test class is run.
     *
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public static function setUpBeforeClass()
    {
        stream_wrapper_register('dummyfs', Internal\DummyFileSystemStream::class);
    }

    /**
     * @throws ZipException
     * @throws \Exception
     */
    public function testDummyFS()
    {
        $fileContents = str_repeat(base64_encode(random_bytes(12000)), 100);

        // create zip file
        $zip = new ZipFile();
        $zip->addFromString(
            'file.txt',
            $fileContents,
            ZipFile::METHOD_DEFLATED
        );
        $zip->saveAsFile($this->outputFilename);
        $zip->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $stream = fopen('dummyfs://localhost/' . $this->outputFilename, 'rb');
        static::assertNotFalse($stream);
        $zip->openFromStream($stream);
        static::assertSame($zip->getListFiles(), ['file.txt']);
        static::assertSame($zip['file.txt'], $fileContents);
        $zip->close();
    }
}