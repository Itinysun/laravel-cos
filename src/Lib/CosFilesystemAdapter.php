<?php

namespace Itinysun\LaravelCos\Lib;

use DateTimeInterface;
use Exception;
use Illuminate\Support\Carbon;
use Itinysun\LaravelCos\Data\FileCopyAttr;
use Itinysun\LaravelCos\Enums\ObjectAcl;
use Itinysun\LaravelCos\Exceptions\CosFilesystemException;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use League\Flysystem\Visibility;
use function stream_get_contents;

class CosFilesystemAdapter implements FilesystemAdapter, TemporaryUrlGenerator,PublicUrlGenerator
{
    protected LaravelCos $cos;

    /**
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        $this->cos = new LaravelCos($config['config_name'] ?? 'default');
    }

    public function fileExists(string $path): bool
    {
        return $this->cos->exists($path);
    }

    public function directoryExists(string $path): bool
    {
        return $this->cos->directoryExists($path);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->write($path, stream_get_contents($contents), $config);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        try {
            $this->cos->uploadData($path, $contents, config: $config->toArray());
            
            // 处理文件可见性设置
            $visibility = $config->get('visibility');
            if ($visibility !== null) {
                $this->setVisibility($path, $visibility);
            }
        } catch (UnableToWriteFile $e) {
            throw new UnableToWriteFile($path, $e);
        }
    }

    public function readStream(string $path): mixed
    {
        $contents = $this->read($path);
        try {
            // 使用 fopen 打开一个内存资源，以读写模式（'r+'）打开
            $stream = fopen('php://memory', 'rb+');
            // 将字符串写入到流中
            fwrite($stream, $contents);
            // 将文件指针重置到流的开头，以便可以从头开始读取
            rewind($stream);

            return $stream;
        } catch (Exception $e) {
            throw new CosFilesystemException($path, $e);
        }
    }

    public function read(string $path): string
    {
        try {
            return $this->cos->getData($path);
        } catch (Exception $e) {
            throw new UnableToReadFile($path, $e->getCode(), $e);
        }
    }

    public function delete(string $path): void
    {
        try {
            $this->cos->delete($path);
        } catch (Exception $e) {
            throw new UnableToDeleteFile($path, $e);
        }
    }

    public function deleteDirectory(string $path): void
    {
        try {
            $this->cos->deleteDirectory($path);
        } catch (Exception $e) {
            throw new UnableToDeleteDirectory($path, $e);
        }
    }

    public function createDirectory(string $path, Config $config): void
    {
        $this->cos->createDirectory($path);
    }

    public function setVisibility(string $path, string $visibility): void
    {
        try {
            $this->cos->setFileAcl($path, ObjectAcl::fromVisibility($visibility));
        } catch (Exception $e) {
            throw new CosFilesystemException('Set visibility failed: ' . $e->getMessage());
        }
    }

    public function visibility(string $path): FileAttributes
    {
        try {
            $acl = $this->cos->getFileAcl($path);
            $prefixer = $this->cos->getPrefixer();
            $prefixedPath = $prefixer->prefixPath($path);
            if ($acl === ObjectAcl::PUBLIC_READ) {
                return new FileAttributes($prefixedPath, null, Visibility::PUBLIC);
            }
            {
                return new FileAttributes($prefixedPath, null, Visibility::PRIVATE);
            }
        } catch (Exception $e) {
            throw new CosFilesystemException('Get visibility failed: ' . $e->getMessage());
        }
    }

    public function mimeType(string $path): FileAttributes
    {
        $attr = $this->cos->getFileAttr($path);
        if ($attr === null || empty($attr->contentType)) {
            throw new UnableToRetrieveMetadata('Get mime type failed: ' . $path);
        }
        return $attr->toFileAttributes();

    }

    public function lastModified(string $path): FileAttributes
    {
        $attr = $this->cos->getFileAttr($path);
        if ($attr && $attr->lastModified != null) {
            return $attr->toFileAttributes();
        }

        throw new UnableToRetrieveMetadata('Get last modified failed: ' . $path);
    }

    public function fileSize(string $path): FileAttributes
    {
        $attr = $this->cos->getFileAttr($path);
        if ($attr && $attr->contentLength > 0) {
            return $attr->toFileAttributes();
        }

        throw new UnableToRetrieveMetadata('Get file size failed: ' . $path);

    }

    public function listContents(string $path, bool $deep): iterable
    {
        try {
            $contents = $this->cos->listObjects($path, $deep);
            if($contents===null)
            {
                return [];
            }
            foreach ($contents->getFiles() as $content) {
                yield new FileAttributes($content->key, $content->size, null, $content->lastModified->timestamp);
            }
            foreach ($contents->getDirs() as $content) {
                yield new FileAttributes($content->key, null, null, null, true);
            }
        } catch (Exception $e) {
            throw new CosFilesystemException('List contents failed: ' . $e->getMessage());
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->cos->move($source, $destination, FileCopyAttr::from($config->toArray()));
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->cos->copy($source, $destination, FileCopyAttr::from($config->toArray()));
    }

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt, Config $config): string
    {
        return $this->cos->tempUrl($path, Carbon::instance($expiresAt), $config->toArray());
    }

    public function getTemporaryUrl(string $path, DateTimeInterface $expiresAt, array $config): string
    {
        return $this->temporaryUrl($path, $expiresAt,new Config($config));
    }

    public function publicUrl(string $path, Config $config): string
    {
        return $this->cos->fixedUrl($path, $config->toArray());
    }

    public function getUrl(string $path): string
    {
        return $this->publicUrl($path, new Config());
    }
}
