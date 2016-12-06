<?php

namespace Hgraca\Lock\Port\FileSystem;

use Hgraca\Lock\Port\FileSystem\Exception\FileNotFoundException;
use Hgraca\Lock\Port\FileSystem\Exception\InvalidPathException;
use Hgraca\Lock\Port\FileSystem\Exception\PathAlreadyExistsException;

interface FileSystemInterface
{
    /**
     * @throws InvalidPathException
     */
    public function fileExists(string $path): bool;

    /**
     * @throws InvalidPathException
     */
    public function deleteFile(string $path): bool;

    /**
     * Creates a folder and all intermediate folders if they don't exist
     *
     * @throws InvalidPathException
     * @throws PathAlreadyExistsException
     */
    public function createDir(string $path): bool;

    /**
     * @throws FileNotFoundException
     * @throws InvalidPathException
     */
    public function readFile(string $path): string;

    /**
     * @throws PathAlreadyExistsException
     * @throws InvalidPathException
     */
    public function writeFile(string $path, string $content);
}
