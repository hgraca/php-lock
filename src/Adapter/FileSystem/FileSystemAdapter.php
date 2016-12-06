<?php

namespace Hgraca\Lock\Adapter\FileSystem;

use Hgraca\FileSystem\Exception\FileNotFoundException;
use Hgraca\FileSystem\Exception\InvalidPathException;
use Hgraca\FileSystem\Exception\PathAlreadyExistsException;
use Hgraca\FileSystem\FileSystemInterface;
use Hgraca\FileSystem\LocalFileSystem;
use Hgraca\Lock\Port\FileSystem\Exception\FileNotFoundException as LockFileNotFoundException;
use Hgraca\Lock\Port\FileSystem\Exception\InvalidPathException as LockInvalidPathException;
use Hgraca\Lock\Port\FileSystem\Exception\PathAlreadyExistsException as LockPathAlreadyExistsException;
use Hgraca\Lock\Port\FileSystem\FileSystemInterface as LockFileSystemInterface;

final class FileSystemAdapter implements LockFileSystemInterface
{
    /**
     * @var FileSystemInterface
     */
    private $fileSystem;

    public function __construct(FileSystemInterface $fileSystem = null)
    {
        $this->fileSystem = $fileSystem ?? new LocalFileSystem(LocalFileSystem::IDEMPOTENT);
    }

    /**
     * @throws LockInvalidPathException
     */
    public function fileExists(string $path): bool
    {
        try {
            return $this->fileSystem->fileExists($path);
        } catch (InvalidPathException $e) {
            throw new LockInvalidPathException('', 0, $e);
        }
    }

    /**
     * @throws LockInvalidPathException
     */
    public function deleteFile(string $path): bool
    {
        try {
            return $this->fileSystem->deleteFile($path);
        } catch (InvalidPathException $e) {
            throw new LockInvalidPathException('', 0, $e);
        }
    }

    /**
     * Creates a folder and all intermediate folders if they don't exist
     *
     * @throws LockInvalidPathException
     * @throws LockPathAlreadyExistsException
     */
    public function createDir(string $path): bool
    {
        try {
            return $this->fileSystem->createDir($path);
        } catch (InvalidPathException $e) {
            throw new LockInvalidPathException('', 0, $e);
        } catch (PathAlreadyExistsException $e) {
            throw new LockPathAlreadyExistsException('', 0, $e);
        }
    }

    /**
     * @throws LockFileNotFoundException
     * @throws LockInvalidPathException
     */
    public function readFile(string $path): string
    {
        try {
            return $this->fileSystem->readFile($path);
        } catch (InvalidPathException $e) {
            throw new LockInvalidPathException('', 0, $e);
        } catch (FileNotFoundException $e) {
            throw new LockFileNotFoundException('', 0, $e);
        }
    }

    /**
     * @throws LockPathAlreadyExistsException
     * @throws LockInvalidPathException
     */
    public function writeFile(string $path, string $content)
    {
        try {
            $this->fileSystem->writeFile($path, $content);
        } catch (InvalidPathException $e) {
            throw new LockInvalidPathException('', 0, $e);
        } catch (PathAlreadyExistsException $e) {
            throw new LockPathAlreadyExistsException('', 0, $e);
        }
    }
}
