<?php

namespace Hgraca\Lock\Test;

use Hgraca\FileSystem\InMemoryFileSystem;
use Hgraca\Lock\Adapter\FileSystem\FileSystemAdapter;
use Hgraca\Lock\Lock;
use Hgraca\Lock\Port\FileSystem\Exception\FileNotFoundException;
use Mockery;
use PHPUnit_Framework_TestCase;

final class LockTest extends PHPUnit_Framework_TestCase
{
    /** @var string */
    private $defaultLockPath = __DIR__ . '/default.lock';

    public function testAcquire_existsAndIsMine()
    {
        list($lock, $fileSystem) = $this->createRealFileSystemAndLock();

        $fileSystem->writeFile($this->defaultLockPath, getmypid());

        self::assertTrue($lock->acquire());
    }

    public function testAcquire_existsButIsNotMine()
    {
        list($lock, $fileSystem) = $this->createRealFileSystemAndLock();

        $fileSystem->writeFile($this->defaultLockPath, '123098');

        self::assertFalse($lock->acquire());
    }

    public function testAcquire_CreateLock_Success()
    {
        list($lock, $fileSystem) = $this->createRealFileSystemAndLock();

        self::assertTrue($lock->acquire());

        self::assertEquals(getmypid(), $fileSystem->readFile($this->defaultLockPath));
    }

    /**
     * @expectedException \Hgraca\Lock\Exception\CouldNotCreateLockException
     */
    public function testAcquire_CreateLock_LockIsNotCreated()
    {
        $fileSystem = Mockery::mock(InMemoryFileSystem::class, [InMemoryFileSystem::IDEMPOTENT]);
        $lock = new Lock('default', __DIR__, 'lock', new FileSystemAdapter($fileSystem));

        $fileSystem->shouldReceive('createDir');
        $fileSystem->shouldReceive('writeFile');
        $fileSystem->shouldReceive('fileExists')->andReturn(false);

        $lock->acquire();
    }

    /**
     * @expectedException \Hgraca\Lock\Exception\CouldNotCreateLockException
     */
    public function testAcquire_CreateLock_LockIsNotMine()
    {
        $fileSystem = Mockery::mock(InMemoryFileSystem::class, [InMemoryFileSystem::IDEMPOTENT]);
        $lock = new Lock('default', __DIR__, 'lock', new FileSystemAdapter($fileSystem));

        $fileSystem->shouldReceive('createDir');
        $fileSystem->shouldReceive('writeFile');
        $fileSystem->shouldReceive('fileExists')->once()->andReturn(false);
        $fileSystem->shouldReceive('fileExists')->once()->andReturn(true);
        $fileSystem->shouldReceive('readFile')->andReturn('123098');

        $lock->acquire();
    }

    /**
     * @expectedException \Hgraca\Lock\Exception\LockNotFoundException
     */
    public function testAcquire_CreateLock_LockIsNotFoundWhenReadingPid()
    {
        $fileSystem = Mockery::mock(InMemoryFileSystem::class, [InMemoryFileSystem::IDEMPOTENT]);
        $lock = new Lock('default', __DIR__, 'lock', new FileSystemAdapter($fileSystem));

        $fileSystem->shouldReceive('createDir');
        $fileSystem->shouldReceive('writeFile');
        $fileSystem->shouldReceive('fileExists')->once()->andReturn(false);
        $fileSystem->shouldReceive('fileExists')->once()->andReturn(true);
        $fileSystem->shouldReceive('readFile')->andThrow(FileNotFoundException::class);

        $lock->acquire();
    }

    public function testRelease_Success()
    {
        list($lock, $fileSystem) = $this->createRealFileSystemAndLock();

        $lock->acquire();
        self::assertTrue($fileSystem->fileExists($this->defaultLockPath));

        self::assertTrue($lock->release());
        self::assertFalse($fileSystem->fileExists($this->defaultLockPath));
    }

    public function testRelease_Success_LockDoesNotExist()
    {
        list($lock) = $this->createRealFileSystemAndLock();

        self::assertTrue($lock->release());
    }

    /**
     * @expectedException \Hgraca\Lock\Exception\CouldNotReleaseLockException
     */
    public function testRelease_CouldNotReleaseLock()
    {
        $fileSystem = Mockery::mock(InMemoryFileSystem::class, [InMemoryFileSystem::IDEMPOTENT]);
        $lock = new Lock('default', __DIR__, 'lock', new FileSystemAdapter($fileSystem));

        $fileSystem->shouldReceive('fileExists')->andReturn(true);
        $fileSystem->shouldReceive('deleteFile')->andReturn(true);

        $lock->release();
    }

    private function createRealFileSystemAndLock(): array
    {
        $fileSystem = new InMemoryFileSystem(InMemoryFileSystem::IDEMPOTENT);
        $lock = new Lock('default', __DIR__, 'lock', new FileSystemAdapter($fileSystem));

        return [$lock, $fileSystem];
    }
}
