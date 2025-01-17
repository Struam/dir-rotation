<?php

namespace Struam\DirRotation\Tests;

use PHPUnit\Framework\TestCase;
use Struam\DirRotation\DirRotation;

class DirRotationTest extends TestCase
{
    private $workingDirectory;
    private $dirRotation;

    protected function setUp(): void
    {

        // Creating a temporary directory for testing
        $this->workingDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dir_rotation_test';
        mkdir($this->workingDirectory, 0755, true);

        // Initializing the DirRotation class
        $this->dirRotation = new DirRotation($this->workingDirectory);
    }

    protected function tearDown(): void
    {

        // Deleting the temporary directory after testing
        $this->deleteDirectory($this->workingDirectory);
    }

    public function testCreateSubdirectory()
    {
        $path = 'test/subdirectory';
        $fullPath = $this->dirRotation->createSubdirectory($path);

        $this->assertDirectoryExists($fullPath);
        $this->assertDirectoryIsWritable($fullPath);
    }

    public function testDeleteSubdirectory()
    {
        $path = 'test/subdirectory';
        $fullPath = $this->dirRotation->createSubdirectory($path);

        $this->dirRotation->deleteSubdirectory($path);
        $this->assertDirectoryDoesNotExist($fullPath);
    }

    public function testChangeWorkingDirectory()
    {
        $newWorkingDirectory = $this->workingDirectory . DIRECTORY_SEPARATOR . 'new_working_dir';
        mkdir($newWorkingDirectory, 0755, true);

        $this->dirRotation->changeWorkingDirectory($newWorkingDirectory);
        $this->assertEquals($newWorkingDirectory, $this->dirRotation->getWorkingDirectory());
    }

    public function testArchiveDirectory()
    {
        $path = 'test/subdirectory';
        $fullPath = $this->dirRotation->createSubdirectory($path);

        $archivePath = $this->dirRotation->archiveDirectory($path);
        $this->assertFileExists($archivePath);
        $this->assertFileIsReadable($archivePath);

        // Deleting the archive after testing
        unlink($archivePath);
    }

    public function testArchiveDirectoryWithDelete()
    {
        $path = 'test/subdirectory';
        $fullPath = $this->dirRotation->createSubdirectory($path);

        $archivePath = $this->dirRotation->archiveDirectory($path, true);
        $this->assertFileExists($archivePath);
        $this->assertFileIsReadable($archivePath);
        $this->assertDirectoryDoesNotExist($fullPath);

        // Deleting the archive after testing
        unlink($archivePath);
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $items = array_diff(scandir($dir), array('.', '..'));
        foreach ($items as $item) {
            $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
        }

        return rmdir($dir);
    }
}
