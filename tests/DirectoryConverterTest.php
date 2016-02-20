<?php

namespace Spatie\Php7to5\Test;

use Illuminate\Filesystem\Filesystem;
use Spatie\Php7to5\DirectoryConverter;

class DirectoryConverterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initializeTempDirectory();
    }

    /** @test */
    public function it_can_copy_and_convert_an_entire_directory()
    {
        $directoryConverter = new DirectoryConverter($this->getSourceDirectory());

        $directoryConverter->savePhp5FilesTo($this->getTempDirectory());

        $this->assertTempFileExists([
            'sourceDirectory/file1.php',
            'sourceDirectory/file2.php',
            'sourceDirectory/file3.txt',
            'sourceDirectory/directory1/file1.php',
            'sourceDirectory/directory1/file2.php',
            'sourceDirectory/directory1/file3.txt',
        ]);

        $this->assertAllPhpFilesWereConverted($this->getTempDirectory());
    }

    /** @test */
    public function it_can_copy_and_convert_an_entire_directory_filtering_on_php_files()
    {
        $directoryConverter = new DirectoryConverter($this->getSourceDirectory());

        $directoryConverter
            ->doNotCopyNonPhpFiles()
            ->savePhp5FilesTo($this->getTempDirectory());

        $this->assertTempFileExists([
            'sourceDirectory/file1.php',
            'sourceDirectory/file2.php',
            'sourceDirectory/directory1/file1.php',
            'sourceDirectory/directory1/file2.php',
        ]);

        $this->assertTempFileNotExists([
            'sourceDirectory/file3.txt',
            'sourceDirectory/directory1/file3.txt',
        ]);

        $this->assertAllPhpFilesWereConverted($this->getTempDirectory());
    }

    public function initializeTempDirectory()
    {
        (new Filesystem())->deleteDirectory($this->getTempDirectory());

        mkdir($this->getTempDirectory());

        $this->addGitignoreTo($this->getTempDirectory());
    }

    public function addGitignoreTo($directory)
    {
        $fileName = "{$directory}/.gitignore";

        $fileContents = '*'.PHP_EOL.'!.gitignore';

        file_put_contents($fileName, $fileContents);
    }

    public function getTempDirectory() : string
    {
        return __DIR__.'/stubs/temp';
    }

    public function getSourceDirectory() : string
    {
        return __DIR__ . '/stubs/directoryConverter';
    }

    protected function assertTempFileExists(array $files)
    {
        foreach($files as $file) {
            $this->assertFileExists("{$this->getTempDirectory()}/{$file}");
        }
    }

    protected function assertTempFileNotExists(array $files)
    {
        foreach($files as $file) {
            $this->assertFileNotExists("{$this->getTempDirectory()}/{$file}");
        }
    }

    protected function assertAllPhpFilesWereConverted(string $directory)
    {
        $convertedPhpFileContents = file_get_contents(__DIR__ . '/stubs/converter/it-can-remove-declarations-statement/php5.php');

        $allFiles = (new Filesystem())->allFiles($directory);

        foreach($allFiles as $file) {
            if ($file->getExtension() == 'php') {
                $this->assertSame(trim($convertedPhpFileContents), file_get_contents($file->getRealPath()));
            }
        }
    }
}
