<?php
namespace Hgraca\Lock;

use Hgraca\Lock\Exception\CouldNotCreateLockException;
use Hgraca\Lock\Exception\CouldNotReleaseLockException;
use Hgraca\Lock\Exception\LockNotFoundException;
use SplFileObject;

class Lock
{
    /** @var string */
    private $lockPath;

    /** @var string */
    private $lockName;

    /** @var string */
    private $lockExt;

    public function __construct(string $lockName = 'default', string $lockPath = null, string $lockExt = 'lock')
    {
        $this->lockName = $lockName;
        $this->lockPath = $lockPath ?? sys_get_temp_dir();
        $this->lockExt  = $lockExt;
    }

    public function acquire(): bool
    {
        if ($this->lockExists()) {
            return $this->isMine();
        }

        return $this->createLock();
    }

    public function release(): bool
    {
        $lockFilePath = $this->getLockFilePath();
        if (file_exists($lockFilePath)) {
            @unlink($lockFilePath);
        }

        if (file_exists($lockFilePath)) {
            throw new CouldNotReleaseLockException();
        }

        return true;
    }

    private function lockExists(): bool
    {
        return file_exists($this->getLockFilePath());
    }

    private function isMine(): bool
    {
        return getmypid() === $this->getLockPid();
    }

    private function getLockPid(): int
    {
        if (! $this->lockExists()) {
            throw new LockNotFoundException();
        }

        $fileObj   = new SplFileObject($this->getLockFilePath());
        $firstLine = $fileObj->fgets();

        return (int) $firstLine;
    }

    /**
     * @throws \Exception
     */
    private function createLock(string $myPid = null): bool
    {
        $oldUmask = umask(0);
        mkdir($this->lockPath, 0777, true);
        umask($oldUmask);

        @file_put_contents($this->getLockFilePath(), $myPid ?? getmypid(), FILE_APPEND);

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
}
