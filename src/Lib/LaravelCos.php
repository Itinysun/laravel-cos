<?php

namespace Itinysun\LaravelCos\Lib;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Itinysun\LaravelCos\Data\FileAttr;
use Itinysun\LaravelCos\Data\FileCopyAttr;
use Itinysun\LaravelCos\Data\ImageInfo;
use Itinysun\LaravelCos\Data\ListData;
use Itinysun\LaravelCos\Enums\ObjectAcl;
use Itinysun\LaravelCos\Enums\StorageClass;
use Itinysun\LaravelCos\Exceptions\CosFilesystemException;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use Qcloud\Cos\Client;
use Qcloud\Cos\Exception\ServiceResponseException;
use RuntimeException;
use Throwable;

readonly class LaravelCos
{
    protected Client $client;

    protected array $config;

    protected string $bucket;

    protected PathPrefixer $prefixer;

    /**
     * @throws CosFilesystemException
     */
    public function __construct($configName = null)
    {
        try {
            if (!$configName) {
                $config = config('cos.default');
            } else {
                $config = config('cos.' . $configName);
            }
            if (empty($config)) {
                throw new RuntimeException('you have to set cos config in config/cos.php');
            }
            $this->client = new Client([
                'region' => $config['region'],
                'schema' => $config['use_https'] ? 'https' : 'http',
                'credentials' => [
                    'secretId' => $config['secret_id'],
                    'secretKey' => $config['secret_key'],
                ],
            ]);
            $this->bucket = $config['bucket'] . '-' . $config['app_id'];
            $this->config = $config;
            $this->prefixer = new PathPrefixer($config['prefix'] ?? '');
        } catch (Exception $e) {
            throw new CosFilesystemException('can not create cos client: ' . $e->getMessage());
        }
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }

    public function getPrefixer(): PathPrefixer
    {
        return $this->prefixer;
    }


    public function getFileAttr($key): ?FileAttr
    {
        try {
            $result = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            $data = $result->toArray();
            Log::debug('getFileAttr', ['data' => $data]);
            return FileAttr::from($data);
        } catch (Exception $e) {
            report($e);
            return null;
        }
    }

    public function directoryExists(string $path): bool
    {
        $prefixedPath = $this->prefixer->prefixDirectoryPath($path);
        $attr = $this->getFileAttr($prefixedPath);
        return $attr && $attr->key === $prefixedPath;
    }

    public function createDirectory(string $key): void
    {
        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => Str::finish($key, '/'),
                'Body' => '',
            ]);
        } catch (Exception $e) {
            report($e);
            throw new UnableToCreateDirectory('create directory failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws UnableToDeleteDirectory
     */
    public function deleteDirectory(string $path): void
    {
        try {
            $files = $this->listObjects($path, true)?->getFiles();
            // Delete all files in the directory
            if ($files && !$files->isEmpty()) {
                $list = $files->map(function ($file) {
                    return [
                        'Key' => $file->key,
                    ];
                });
                Log::warning('delete directory', ['path' => $path, 'sum' => $list->count(), 'list' => $list]);
                $this->client->deleteObjects([
                    'Bucket' => $this->bucket,
                    'Objects' => $list->toArray(),
                ]);
            }
            // Delete the directory itself
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => Str::finish($path, '/'),
            ]);
        } catch (Exception $e) {
            report($e);
            throw new UnableToDeleteDirectory('Delete directory failed: ' . $e->getMessage());
        }
    }

    public function listObjects(string $directory = '', bool $recursive = false): ?ListData
    {
        try {
            $prefixedPath = $this->prefixer->prefixDirectoryPath($directory);
            $result = $this->client->listObjects([
                'Bucket' => $this->bucket,
                'Delimiter' => $recursive ? '' : '/',
                'MaxKeys' => 1000,
                'Prefix' => $prefixedPath,
            ]);
            $data = $result->toArray();
        } catch (ServiceResponseException $e) {
            report($e);
            return null;
        }
        if (!$data && !is_array($data)) {
            return null;
        }
        try {
            $list = ListData::from($data);
            if ($list->isTruncated) {
                throw new CosFilesystemException('List is truncated, please use pagination');
            }
            return $list;
        } catch (Exception $e) {
            Log::error('Error when parsing listObjects response: ' . $e->getMessage());
            report($e);
            return null;
        }
    }

    public function exists(string $path): bool
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        $attr = $this->getFileAttr($prefixedPath);
        return $attr && $attr->key === $prefixedPath;
    }

    /**
     * @throws Exception
     */
    public function uploadFile($key, $filePath): void
    {
        $prefixedPath = $this->prefixer->prefixPath($key);
        if (!file_exists($filePath)) {
            throw new FileNotFoundException("File not found: $filePath");
        }
        $handle = fopen($filePath, 'rb');
        $this->client->upload($this->bucket, $prefixedPath, $handle);
        if (is_resource($handle)) {
            @fclose($handle);
        }
    }

    /**
     * @throws UnableToWriteFile
     *
     * @see https://cloud.tencent.com/document/product/436/64283#.E7.AE.80.E5.8D.95.E4.B8.8A.E4.BC.A0.E5.AF.B9.E8.B1.A1
     */
    public function uploadData(string $key, mixed $data, StorageClass $class = StorageClass::STANDARD, array $config = []): void
    {
        $prefixedPath = $this->prefixer->prefixPath($key);
        try {
            $opt = [
                'Bucket' => $this->bucket,
                'Key' => $prefixedPath,
                'Body' => $data,
            ];
            if ($class && $class !== StorageClass::STANDARD) {
                $opt['StorageClass'] = $class->value;
            }
            if ($config) {
                $opt = array_merge($opt, $config);
            }
            $this->client->putObject($opt);
        } catch (Throwable $e) {
            report($e);
            throw new UnableToWriteFile('Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function download($key, $target): void
    {
        $prefixedPath = $this->prefixer->prefixPath($key);
        try {
            $this->client->download($this->bucket, $prefixedPath, $target, [
                'PartSize' => 20 * 1024 * 1024, // 分块大小
                'Concurrency' => 1, // 并发数
            ]);
        } catch (Throwable $e) {
            report($e);
            throw new UnableToReadFile('Download failed: ' . $e->getMessage());
        }
    }

    public function getData($key)
    {
        $prefixedPath = $this->prefixer->prefixPath($key);
        try {
            $result = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $prefixedPath,
            ]);

            return $result['Body'];
        } catch (Exception $e) {
            report($e);
            throw UnableToReadFile::fromLocation($prefixedPath, (string)$e);
        }
    }

    /**
     * @throws Exception
     */
    public function picInfo($key): ImageInfo
    {
        $prefixedPath = $this->prefixer->prefixPath($key);
        try {
            $result = $this->client->ImageInfo([
                'Bucket' => $this->bucket,
                'Key' => $prefixedPath,
            ]);

            return ImageInfo::from($result['Data']);
        } catch (Exception $e) {
            report($e);
            throw new RuntimeException('Get pic info failed: ' . $e->getMessage());
        }
    }

    public function move($from, $to, ?FileCopyAttr $attr = null): void
    {

        try {
            $this->copy($from, $to, $attr);
            $this->delete($from);
        } catch (Exception $e) {
            throw new UnableToMoveFile('Move failed: ' . $e->getMessage());
        }
    }

    public function copy($from, $to, ?FileCopyAttr $attr = null): void
    {
        $prefixedFrom = $this->prefixer->prefixPath($from);
        $prefixedTo = $this->prefixer->prefixPath($to);
        try {
            $data = [
                'Region' => $this->config['region'],
                'Bucket' => $this->bucket,
                'Key' => $prefixedFrom,
            ];
            if ($attr) {
                $data = array_merge($data, $attr->toArray());
                //if we have metadata,original file's metadata will be replaced
                //if we don't have metadata,original file's metadata will be copied
                if (!empty($attr->metadata)) {
                    $data['MetadataDirective'] = 'REPLACE';
                } else {
                    $data['MetadataDirective'] = 'COPY';
                }
            }
            $this->client->copy($this->bucket, $prefixedTo, $data);
        } catch (Exception $e) {
            report($e);
            throw new UnableToCopyFile('Copy failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function delete(string $path): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $prefixedPath,
            ]);
        } catch (ServiceResponseException $e) {
            report($e);
            throw new UnableToDeleteFile('Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws CosFilesystemException
     */
    public function setFileAcl(string $key, ObjectAcl $acl): void
    {
        $prefixedPath = $this->prefixer->prefixPath($key);
        try {
            $this->client->putObjectAcl([
                'Bucket' => $this->bucket,
                'Key' => $prefixedPath,
                'ACL' => $acl->value,
            ]);
        } catch (Exception $e) {
            report($e);
            throw new CosFilesystemException('setFileAcl failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws CosFilesystemException
     */
    public function getFileAcl(string $key): ObjectAcl
    {
        $prefixedPath = $this->prefixer->prefixPath($key);
        try {
            $result = $this->client->getObjectAcl([
                'Bucket' => $this->bucket,
                'Key' => $prefixedPath,
            ]);
            $grants = Arr::get($result->toArray(), 'Grants', []);
        } catch (Exception $e) {
            throw new CosFilesystemException('getFileAcl failed: ' . $e->getMessage());
        }
        if (empty($grants)) {
            throw new CosFilesystemException('getFileAcl failed,no grants found');
        }
        try {
            foreach ($grants as $grant) {
                foreach ($grant as $grantValue) {
                    foreach ($grantValue as $value) {
                        $uri = Arr::get($value, 'Grantee.URI', false);
                        if ($uri && Str::endsWith($uri, 'AllUsers')) {
                            $permission = $value['Permission'];

                            return ObjectAcl::fromPermission($permission);
                        }
                    }
                }
            }
            // Default to private if no public permission found
            return ObjectAcl::PRIVATE;
        } catch (Exception $e) {
            throw new CosFilesystemException('getFileAcl failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws CosFilesystemException
     */
    public function setFileAttr($key, FileCopyAttr $attr): void
    {
        $prefixedPath = $this->prefixer->prefixPath($key);
        try {
            $data = [
                'Key' => $prefixedPath,
                'Bucket' => $this->bucket,
                'CopySource' => $this->buildFullPath($key),
                'MetadataDirective' => 'REPLACE',
            ];
            $options = $attr->toArray();
            Log::debug('setFileAttr', ['data' => $data, 'options' => $options]);
            $this->client->copyObject(array_merge($options, $data));
        } catch (Exception $e) {
            report($e);
            throw new CosFilesystemException('setFileAttr failed: ' . $e->getMessage());
        }
    }

    protected function buildFullPath(string $key, ?string $version = null): string
    {
        $full = "$this->bucket.cos.{$this->config['region']}.myqcloud.com/$key";
        if ($version) {
            $full .= '?versionId=' . $version;
        }
        return $full;
    }


    public function fixedUrl(string $key, array $params = [], array $headers = []): string
    {
        $prefixedPath = $this->prefixer->prefixPath($key);
        $url = $this->client->getObjectUrlWithoutSign($this->bucket, $prefixedPath, ['Params' => $params, 'Headers' => $headers]);
        Log::debug('fixUrl', ['url' => $url]);
        return $url;
    }

    public function tempUrl(string $key, ?Carbon $expireAt, array $params = [], array $headers = []): string
    {
        $prefixedPath = $this->prefixer->prefixPath($key);
        $expireAt = $expireAt ?? Carbon::now()->addMinutes(30);
        $url = $this->client->getObjectUrl($this->bucket, $prefixedPath, $expireAt->toDateTimeString(), ['Params' => $params, 'Headers' => $headers]);
        Log::debug('tempUrl', ['url' => $url]);
        return $url;
    }
}
