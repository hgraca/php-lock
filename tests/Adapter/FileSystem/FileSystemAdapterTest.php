<?php

namespace Hgraca\Lock\Test\Adapter\FileSystem;

use Hgraca\FileSystem\Exception\FileNotFoundException;
use Hgraca\FileSystem\Exception\InvalidPathException;
use Hgraca\FileSystem\Exception\PathAlreadyExistsException;
use Hgraca\FileSystem\FileSystemInterface;
use Hgraca\FileSystem\InMemoryFileSystem;
use Hgraca\Lock\Adapter\FileSystem\FileSystemAdapter;
use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase;

final class FileSystemAdapterTest extends PHPUnit_Framework_TestCase
{
    /** @var MockInterface|FileSystemInterface */
    private $fileSystem;

    /** @var FileSystemAdapter */
    private $adapter;

    public function setUp()
    {
        $this->fileSystem = Mockery::mock(InMemoryFileSystem::class, [InMemoryFileSystem::IDEMPOTENT]);
        $this->adapter = new FileSystemAdapter($this->fileSystem);
    }

    public function testFileExists()
    {
        $pathExists = __DIR__ . '/file.txt';
        $pathDoesNotExist = __DIR__ . '/AAAA.txt';
        $this->fileSystem->shouldReceive('fileExists')->with($pathExists)->andReturn(true);
        $this->fileSystem->shouldReceive('fileExists')->with($pathDoesNotExist)->andReturn(false);

        self::assertTrue($this->adapter->fileExists($pathExists));
        self::assertFalse($this->adapter->fileExists($pathDoesNotExist));
    }

    /**
     * @expectedException \Hgraca\Lock\Port\FileSystem\Exception\InvalidPathException
     */
    public function testFileExists_throwsException()
    {
        $path = 'some/path';
        $this->fileSystem->shouldReceive('fileExists')->with($path)->andThrow(InvalidPathException::class);

        $this->adapter->fileExists($path);
    }

    public function testDeleteFile()
    {
        $path = 'some/path';
        $this->fileSystem->shouldReceive('deleteFile')->with($path)->andReturn(true);

        self::assertTrue($this->adapter->deleteFile($path));
    }

    /**
     * @expectedException \Hgraca\Lock\Port\FileSystem\Exception\InvalidPathException
     */
    public function testDeleteFile_throwsException()
    {
        $path = 'some/path';
        $this->fileSystem->shouldReceive('deleteFile')->with($path)->andThrow(InvalidPathException::class);

        $this->adapter->deleteFile($path);
    }

    public function testCreateDir()
    {
        $path = 'some/path';
        $this->fileSystem->shouldReceive('createDir')->with($path)->andReturn(true);

        self::assertTrue($this->adapter->createDir($path));
    }

    /**
     * @expectedException \Hgraca\Lock\Port\FileSystem\Exception\InvalidPathException
     */
    public function testCreateDir_throwsInvalidPathException()
    {
        $path = 'some/path';
        $this->fileSystem->shouldReceive('createDir')->with($path)->andThrow(InvalidPathException::class);

        $this->adapter->createDir($path);
    }

    /**
     * @expectedException \Hgraca\Lock\Port\FileSystem\Exception\PathAlreadyExistsException
     */
    public function testCreateDir_throwsPathAlreadyExistsException()
    {
        $path = 'some/path';
        $this->fileSystem->shouldReceive('createDir')->with($path)->andThrow(PathAlreadyExistsException::class);

        $this->adapter->createDir($path);
    }

    public function testReadFile()
    {
        $path = 'some/path';
        $contents = '123';
        $this->fileSystem->shouldReceive('readFile')->with($path)->andReturn($contents);

        self::assertEquals($contents, $this->adapter->readFile($path));
    }

    /**
     * @expectedException \Hgraca\Lock\Port\FileSystem\Exception\InvalidPathException
     */
    public function testReadFile_throwsInvalidPathException()
    {
        $path = 'some/path';
        $this->fileSystem->shouldReceive('readFile')->with($path)->andThrow(InvalidPathException::class);

        $this->adapter->readFile($path);
    }

    /**
     * @expectedException \Hgraca\Lock\Port\FileSystem\Exception\FileNotFoundException
     */
    public function testReadFile_throwsFileNotFoundException()
    {
        $path = 'some/path';
        $this->fileSystem->shouldReceive('readFile')->with($path)->andThrow(FileNotFoundException::class);

        $this->adapter->readFile($path);
    }

    public function testWriteFile()
    {
        $path = 'some/path';
        $contents = '123';
        $this->fileSystem->shouldReceive('writeFile')->once()->with($path, $contents);

        $this->adapter->writeFile($path, $contents);
    }

    /**
     * @expectedException \Hgraca\Lock\Port\FileSystem\Exception\InvalidPathException
     */
    public function testWriteFile_throwsInvalidPathException()
    {
        $path = 'some/path';
        $contents = '123';
        $this->fileSystem->shouldReceive('writeFile')->with($path, $contents)->andThrow(InvalidPathException::class);

        $this->adapter->writeFile($path, $contents);
    }

    /**
     * @expectedException \Hgraca\Lock\Port\FileSystem\Exception\PathAlreadyExistsException
     */
    public function testWriteFile_throwsPathAlreadyExistsException()
    {
        $path = 'some/path';
        $contents = '123';
        $this->fileSystem->shouldReceive('writeFile')->with($path, $contents)->andThrow(PathAlreadyExistsException::class);

        $this->adapter->writeFile($path, $contents);
    }
}
