<?php

declare(strict_types=1);

namespace Struam\DirRotation;

use Exception;
use ZipArchive;

class DirRotation
{

    /**
     * @var string
     */
    private string $workingDirectory;

    /**
     * @param string|null $workingDirectory
     * @throws Exception
     */
    public function __construct(string $workingDirectory = null)
    {
        $this->setWorkingDirectory($workingDirectory);
    }

    /**
     * Returns the current working directory
     *
     * @return string Absolute path to the working directory
     */
    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * Change the current working directory
     *
     * @param string $workingDirectory Relative path to the new directory
     * @return void
     * @throws Exception
     */
    public function changeWorkingDirectory(string $workingDirectory): void
    {
        $this->setWorkingDirectory($workingDirectory);
    }

    /**
     * Recursive creation of the specified directories in the working directory
     *
     * @param string $path Name of the new directory. Example: new-dir/sub-folder/some-folder
     * @return string Absolute path to the created directory. Example: /var/project/working-dir/new-dir/sub-folder/some-folder/
     * @throws Exception
     */
    public function createSubdirectory(string $path): string
    {

        // Splitting the input string into subdirectories
        $subdirectories = explode('/', $path);

        // Checking each subdirectory for prohibited characters
        foreach ($subdirectories as $subdirectory) {
            if (!$this->isValidDirectoryName($subdirectory)) {
                throw new Exception("Directory name contains forbidden characters: $subdirectory");
            }
        }

        // Full path to the new subdirectory
        $fullPath = $this->workingDirectory . DIRECTORY_SEPARATOR . $path;

        // Checking if a directory exists
        if (is_dir($fullPath)) {
            throw new Exception("Directory already exists: $fullPath");
        }

        // Creating a directory with recursive creation of parent directories
        if (!mkdir($fullPath, 0755, true)) {
            throw new Exception("Failed to create directory: $fullPath");
        }

        return $fullPath;
    }

    /**
     * Deleting the specified directory including subfolders and files from the working directory
     *
     * @param string $path Relative path to the directory to be deleted
     * @return string Absolute path of the deleted directory
     * @throws Exception
     */
    public function deleteSubdirectory(string $path): string
    {

        // Splitting the input string into subdirectories
        $subdirectories = explode('/', $path);

        // Checking each subdirectory for prohibited characters
        foreach ($subdirectories as $subdirectory) {
            if (!$this->isValidDirectoryName($subdirectory)) {
                throw new Exception("Directory name contains forbidden characters: $subdirectory");
            }
        }

        // Full path to the directory for deletion
        $fullPath = $this->workingDirectory . DIRECTORY_SEPARATOR . $path;

        // Checking the existence of a directory
        if (!is_dir($fullPath)) {
            throw new Exception("Directory does not exist: $fullPath");
        }

        // Recursive deletion of a directory
        if (!$this->deleteDirectory($fullPath)) {
            throw new Exception("Failed to delete directory: $fullPath");
        }

        return $fullPath;
    }

    /**
     * Archiving the specified directory with all its contents in the working directory
     *
     * @param string $path Relative path to the directory to be archived
     * @param bool $deleteAfterArchive Delete the directory after archiving. Default is false
     * @return string Absolute path to the resulting archive
     * @throws Exception
     */
    public function archiveDirectory(string $path,bool $deleteAfterArchive = false): string
    {

        // Splitting the input string into subdirectories
        $subdirectories = explode('/', $path);

        // Checking each subdirectory for forbidden characters
        foreach ($subdirectories as $subdirectory) {
            if (!$this->isValidDirectoryName($subdirectory)) {
                throw new Exception("Directory name contains forbidden characters: $subdirectory");
            }
        }

        // Full path to the directory for archiving
        $fullPath = $this->workingDirectory . DIRECTORY_SEPARATOR . $path;

        // Checking the existence of the directory
        if (!is_dir($fullPath)) {
            throw new Exception("Directory does not exist: $fullPath");
        }

        // Path to the parent directory
        $parentDirectory = dirname($fullPath);

        // Archive name
        $archiveName = basename($fullPath) . '.zip';

        // Full path to the archive
        $archivePath = $parentDirectory . DIRECTORY_SEPARATOR . $archiveName;

        // Creating a ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Failed to create archive: $archivePath");
        }

        // Adding all files and subdirectories to the archive
        $this->addFilesToZip($zip, $fullPath, basename($fullPath));

        // Closing the archive
        $zip->close();

        // Deleting the directory after archiving, if specified
        if ($deleteAfterArchive) {
            $this->deleteSubdirectory($path);
        }

        return $archivePath;
    }

    /**
     * Initialization of the working directory
     *
     * @param string $workingDirectory Absolute or relative path to the working directory
     * @return void
     * @throws Exception
     */
    private function setWorkingDirectory(string $workingDirectory = null): void
    {

        // If no directory is specified, set the current working directory
        if ($workingDirectory === null) {
            $workingDirectory = getcwd();
        }

        // Converting a relative path to an absolute path
        $workingDirectory = realpath($workingDirectory);

        // Checking that realpath returned a valid path
        if ($workingDirectory === false) {
            throw new Exception("Invalid directory path provided");
        }

        // Checking if a directory exists
        if (!is_dir($workingDirectory)) {
            throw new Exception("Directory does not exist: $workingDirectory");
        }

        // Checking if a directory is writable
        if (!is_writable($workingDirectory)) {
            throw new Exception("Directory is not writable: $workingDirectory");
        }

        $this->workingDirectory = $workingDirectory;
    }

    /**
     * Checking the directory name for prohibited characters
     *
     * @param string $dir Directory name
     * @return bool
     */
    private function isValidDirectoryName(string $dir): bool
    {

        // Regular expression to find prohibited characters
        $forbiddenPattern = '/[\/\\:\*\?"<>|#]/u';

        // Checking if the string contains prohibited characters
        if (preg_match($forbiddenPattern, $dir)) {
            return false;
        }

        return true;
    }

    /**
     * Recursively delete a directory with all its contents
     *
     * @param string $dir Absolute path to the directory to be deleted
     * @return bool
     */
    private function deleteDirectory(string $dir): bool
    {

        // Checking if the path is a directory
        if (!is_dir($dir)) {
            return false;
        }

        // Getting a list of items in a directory
        $items = array_diff(scandir($dir), array('.', '..'));

        // Getting a list of items in a directory
        foreach ($items as $item) {

            // If the item is a directory, recursively delete it
            if (is_dir($dir . DIRECTORY_SEPARATOR . $item)) {
                $this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item);
            } else {

                // If the item is a file, delete it
                unlink($dir . DIRECTORY_SEPARATOR . $item);
            }
        }

        // Deleting the directory itself
        return rmdir($dir);
    }

    /**
     * Recursively adds files and subdirectories to the ZIP archive.
     *
     * @param ZipArchive $zip ZipArchive object.
     * @param string $sourcePath Path to the directory or file to be added to the archive.
     * @param string $localPath Local path in the archive.
     */
    private function addFilesToZip(ZipArchive $zip, string $sourcePath, string $localPath): void
    {

        // Check if the path is a file
        if (is_file($sourcePath)) {

            // Adding a file to the archive
            $zip->addFile($sourcePath, $localPath);
        } elseif (is_dir($sourcePath)) {

            // Adding an empty directory to the archive
            $zip->addEmptyDir($localPath);

            // Getting a list of items in a directory
            $items = array_diff(scandir($sourcePath), array('.', '..'));

            foreach ($items as $item) {

                // Recursively adding an item to the archive
                $this->addFilesToZip($zip, $sourcePath . DIRECTORY_SEPARATOR . $item, $localPath . DIRECTORY_SEPARATOR . $item);
            }
        }
    }
}
