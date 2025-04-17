<?php

namespace Itinysun\LaravelCos;

use DateTimeInterface;
use Itinysun\LaravelCos\Enums\ObjectAcl;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use League\Flysystem\Visibility;

class CosFilesystemAdapter implements FilesystemAdapter, TemporaryUrlGenerator
{
    protected PathPrefixer $prefixer;

    protected LaravelCos $cos;

    /**
     * @throws \Exception
     */
    public function __construct(array $config = [])
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

        $attr = $this->cos->getFileAttr($prefixedPath);
        if($attr->key){
            return true;
        }else
        {
            return false;
        }
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

    public function readStream(string $path): mixed
    {
        $contents = $this->read($path);
        try {
            // 使用 fopen 打开一个内存资源，以读写模式（'r+'）打开
            $stream = fopen('php://memory', 'r+');
            // 将字符串写入到流中
            fwrite($stream, $contents);
            // 将文件指针重置到流的开头，以便可以从头开始读取
            rewind($stream);

            return $stream;
        } catch (\Exception $e) {
            throw new CosFilesystemException($path, $e);
        }
    }

    public function read(string $path): string
    {
        try {
            $prefixedPath = $this->prefixer->prefixPath($path);
            return $this->cos->getData($prefixedPath);
        } catch (\Exception $e) {
            throw new UnableToReadFile($path, $e->getCode(), $e);
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
        $this->cos->createDirectory($path);
    }

    public function setVisibility(string $path, string $visibility): void
    {

        try {
            $prefixedPath = $this->prefixer->prefixPath($path);
            $this->cos->setFileAcl($prefixedPath, ObjectAcl::fromVisibility($visibility));
        } catch (\Exception $e) {
            throw new CosFilesystemException('Set visibility failed: ' . $e->getMessage());
        }
    }

    public function visibility(string $path): FileAttributes
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        try {
            $acl = $this->cos->getFileAcl($prefixedPath);
            if ($acl === ObjectAcl::PUBLIC_READ) {
                return new FileAttributes($path, null, Visibility::PUBLIC);
            }
            {
                return new FileAttributes($path, null, Visibility::PRIVATE);
            }
        } catch (\Exception $e) {
            throw new CosFilesystemException('Get visibility failed: ' . $e->getMessage());
        }
    }

    public function mimeType(string $path): FileAttributes
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        try {
            $attr = $this->cos->getFileAttr($prefixedPath);
            if ($attr->contentType == null) {
                throw new UnableToRetrieveMetadata('Get mime type failed: ' . $path);
            }
            return $attr->toFileAttributes();
        } catch (\Exception $e) {
            throw new CosFilesystemException('Get mime type failed: ' . $e->getMessage());
        }
    }

    public function lastModified(string $path): FileAttributes
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        try {
            $attr = $this->cos->getFileAttr($prefixedPath);
            if ($attr->lastModified == null) {
                throw new UnableToRetrieveMetadata('Get last modified failed: ' . $path);
            }
            return $attr->toFileAttributes();
        } catch (\Exception $e) {
            throw new CosFilesystemException('Get last modified failed: ' . $e->getMessage());
        }
    }

    public function fileSize(string $path): FileAttributes
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        try {
            $attr = $this->cos->getFileAttr($prefixedPath);
            if ($attr->contentLength == null) {
                throw new UnableToRetrieveMetadata('Get file size failed: ' . $path);
            }
            return $attr->toFileAttributes();
        } catch (\Exception $e) {
            throw new CosFilesystemException('Get file size failed: ' . $e->getMessage());
        }
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        try {
            $contents = $this->cos->listObjects($prefixedPath, $deep);
            foreach ($contents->getFiles() as $content) {
                yield new FileAttributes($content->key, $content->size, null, $content->lastModified->timestamp);
            }
            foreach ($contents->getDirs() as $content) {
                yield new FileAttributes($content->key, null, null, null, true);
            }
        } catch (\Exception $e) {
            throw new CosFilesystemException('List contents failed: ' . $e->getMessage());
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->cos->move($source, $destination);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->cos->copy($source, $destination);
    }

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt, Config $config): string
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        return $this->cos->tempUrl($prefixedPath, $expiresAt->getTimestamp(), $config->toArray());
    }
}
