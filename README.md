# DirRotation
`DirRotation` — is a library for managing directories and files in PHP.
It provides methods for creating, deleting, archiving, and changing the working directory.

## Installation
To install the library, use Composer:

```bash
composer require struam/dir-rotation
```

## Usage

### Example of usage
```php
require 'vendor/autoload.php';

use Struam\DirRotation\DirRotation;

try {

    // Initialization without specifying the working directory
    $dirRotation = new DirRotation();
    echo "Working Directory: " . $dirRotation->getWorkingDirectory();

    // Creating a subdirectory
    $subdirectoryPath = $dirRotation->createSubdirectory('dir/new/temp');
    echo "Subdirectory created at: " . $subdirectoryPath;

    // Archiving a subdirectory followed by deletion
    $archivePath = $dirRotation->archiveDirectory('dir/new/temp', true);
    echo "Directory archived at: " . $archivePath;

    // Changing the working directory
    $dirRotation->changeWorkingDirectory('/new/working/directory');
    echo "Working Directory changed to: " . $dirRotation->getWorkingDirectory();

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Methods

#### __construct(string $workingDirectory = null)
Initializes the DirRotation object with the specified working directory.
If the working directory is not specified, the current working directory is used.

 - $workingDirectory (string|null)

Example:

'../', '../dir', '/var/project/dir', 'dir'

#### getWorkingDirectory()
Returns the current working directory.

#### changeWorkingDirectory(string $workingDirectory)
Changes the current working directory to the specified one.

 - $workingDirectory (string)

Example:

'../', '../dir', '/var/project/dir', 'dir'

#### createSubdirectory(string $path)
Creates a subdirectory in the working directory.

 - $path (string)

Example:

'dir', '/dir/new-dir', '/dir/new-dir/some-dir'

#### deleteSubdirectory(string $path)
Deletes a subdirectory from the working directory.

 - $path (string)

Example:

'dir', '/dir/new-dir', '/dir/new-dir/some-dir'

#### archiveDirectory(string $path,bool $deleteAfterArchive = false)
Archives the specified directory and saves the archive in the parent directory.

Example:

Archive directory: '/dir/new-dir'

Current working directory: '/var/project/'

The archive will be saved in the directory: '/var/project/dir/new-dir.zip'

 - $path (string)

The path to the directory to be archived.

Example:

'dir', '/dir/new-dir', '/dir/new-dir/some-dir'

 - $deleteAfterArchive (bool)

If true, the directory will be deleted after archiving. Default is false.

## License
This library is distributed under the MIT License. See the LICENSE file for details.

## Contributing
Contributions and suggestions for improving the library are welcome. Please create an issue or pull request.

## Author
Rustam Kadirov <struam@gmail.com>

## Version History
1.0.0
First release

This `README.md` file provides essential information about the library, including installation, usage,
testing, and licensing. You can adapt it to your needs by adding additional sections or modifying existing ones.
