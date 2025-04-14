<?php

namespace Itinysun\LaravelCos;

use DateTimeInterface;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;

class CosFilesystemAdapter implements FilesystemAdapter, TemporaryUrlGenerator
{
    protected PathPrefixer $prefixer;
    protected LaravelCos $cos;

    /**
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        $this->cos = new LaravelCos($config['config_name'] ?? 'default');
        $this->prefixer = new PathPrefixer($config['prefix'] ?? '');
    }


    public function fileExists(string $path): bool
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        return $this->cos->exists($prefixedPath);
    }

    public function directoryExists(string $path): bool
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        return $this->cos->exists($prefixedPath);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->write($path, \stream_get_contents($contents), $config);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        try {
            $this->cos->uploadData($prefixedPath, $contents, config: $config->toArray());
        } catch (UnableToWriteFile $e) {
            throw new UnableToWriteFile($prefixedPath, $e);
        }
    }

    public function readStream(string $path)
    {
        $contents = $this->read($path);
        try {
            // 使用 fopen 打开一个内存资源，以读写模式（'r+'）打开
            $stream = fopen('php://memory', 'r+');
            // 将字符串写入到流中
            fwrite($stream, $contents);
            // 将文件指针重置到流的开头，以便可以从头开始读取
            fseek($stream, 0);
            return $stream;
        } catch (\Exception $e) {
            throw new CosFilesystemException($prefixedPath, $e);
        }
    }

    public function read(string $path): string
    {
        try {
            $prefixedPath = $this->prefixer->prefixPath($path);
            return $this->cos->getData($prefixedPath);
        } catch (\Exception $e) {
            throw new UnableToReadFile($path, $e);
        }
    }

    public function delete(string $path): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        try {
            $this->cos->delete($prefixedPath);
        } catch (\Exception $e) {
            throw new UnableToDeleteFile($prefixedPath, $e);
        }
    }

    public function deleteDirectory(string $path): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        try {
            $this->cos->deleteDirectory($prefixedPath);
        } catch (\Exception $e) {
            throw new UnableToDeleteDirectory($prefixedPath, $e);
        }
    }

    public function createDirectory(string $path, Config $config): void
    {
        $this->cos->createDirectory($path, $config);
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // TODO: Implement setVisibility() method.
    }

    public function visibility(string $path): FileAttributes
    {
        // TODO: Implement visibility() method.
    }

    public function mimeType(string $path): FileAttributes
    {
        // TODO: Implement mimeType() method.
    }

    public function lastModified(string $path): FileAttributes
    {
        // TODO: Implement lastModified() method.
    }

    public function fileSize(string $path): FileAttributes
    {
        // TODO: Implement fileSize() method.
    }

    public function listContents(string $path, bool $deep): iterable
    {
        // TODO: Implement listContents() method.
    }

    public function move(string $source, string $destination, Config $config): void
    {
        // TODO: Implement move() method.
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        // TODO: Implement copy() method.
    }

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt, Config $config): string
    {
        // TODO: Implement temporaryUrl() method.
    }
}
