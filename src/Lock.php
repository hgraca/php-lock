<?php
namespace Hgraca\Lock;

use Exception;
use Hgraca\Lock\Adapter\FileSystem\FileSystemAdapter;
use Hgraca\Lock\Exception\CouldNotCreateLockException;
use Hgraca\Lock\Exception\CouldNotReleaseLockException;
use Hgraca\Lock\Exception\LockNotFoundException;
use Hgraca\Lock\Port\FileSystem\Exception\InvalidPathException;
use Hgraca\Lock\Port\FileSystem\Exception\PathAlreadyExistsException;
use Hgraca\Lock\Port\FileSystem\FileSystemInterface;

final class Lock
{
    /** @var string */
    private $lockPath;

    /** @var string */
    private $lockName;

    /** @var string */
    private $lockExt;

    /** @var FileSystemInterface */
    private $fileSystem;

    public function __construct(
        string $lockName = 'default',
        string $lockPath = null,
        string $lockExt = 'lock',
        FileSystemInterface $fileSystem = null
    ) {
        $this->lockName   = $lockName;
        $this->lockPath   = $lockPath ?? sys_get_temp_dir();
        $this->lockExt    = $lockExt;
        $this->fileSystem = $fileSystem ?? new FileSystemAdapter();
    }

    public function acquire(): bool
    {
        if ($this->lockExists()) {
            return $this->isMine();
        }

        return $this->createLock();
    }

    /**
     * @throws CouldNotReleaseLockException
     * @throws InvalidPathException
     */
    public function release(): bool
    {
        if ($this->lockExists()) {
            $this->fileSystem->deleteFile($this->getLockFilePath());
        }

        if ($this->lockExists()) {
            throw new CouldNotReleaseLockException();
        }

        return true;
    }

    /**
     * @throws CouldNotCreateLockException
     * @throws InvalidPathException
     * @throws PathAlreadyExistsException
     */
    private function createLock(string $myPid = null): bool
    {
        $this->fileSystem->createDir($this->lockPath);

        $this->fileSystem->writeFile($this->getLockFilePath(), $myPid ?? getmypid());

        if (! $this->lockExists()) {
            throw new CouldNotCreateLockException();
        }

        if (! $this->isMine()) {
            throw new CouldNotCreateLockException('Another process somehow locked before us!');
        }

        return true;
    }

    private function getLockFilePath(): string
    {
        return
            rtrim($this->lockPath, '/')
            . '/'
            . rtrim(ltrim($this->lockName, '/'), '.')
            . '.'
            . ltrim($this->lockExt, '.');
    }

    private function lockExists(): bool
    {
        return $this->fileSystem->fileExists($this->getLockFilePath());
    }

    private function isMine(): bool
    {
        return getmypid() === $this->getLockPid();
    }

    /**
     * @throws LockNotFoundException
     */
    private function getLockPid(): int
    {
        try {
            return $this->fileSystem->readFile($this->getLockFilePath());
        } catch (Exception $e) {
            throw new LockNotFoundException('', 0, $e);
        }
    }
}
