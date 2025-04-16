<?php

namespace Itinysun\LaravelCos;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Itinysun\LaravelCos\Data\FileAttr;
use Itinysun\LaravelCos\Data\ImageInfo;
use Itinysun\LaravelCos\Data\ListData;
use Itinysun\LaravelCos\Enums\ObjectAcl;
use Itinysun\LaravelCos\Enums\StorageClass;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use Qcloud\Cos\Client;
use Qcloud\Cos\Exception\ServiceResponseException;

readonly class LaravelCos
{
    protected Client $client;

    protected array $config;

    protected string $bucket;

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
        } catch (Exception $e) {
            throw new CosFilesystemException('you have to set cos config in config/cos.php');
        }
    }

    /**
     * @throws CosFilesystemException
     */
    public function exists(string $path): bool
    {
        try {
            return $this->client->doesObjectExist($this->bucket, $path);
        } catch (Exception $e) {
            throw new CosFilesystemException('Check file exists failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function delete(string $path): void
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);
        } catch (ServiceResponseException $e) {
            report($e);
            throw new CosFilesystemException('Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function deleteDirectory(string $path): void
    {
        try {
            $files = $this->listObjects($path, true)?->getFiles();
            if ($files && !$files->isEmpty()) {
                $list = $files->map(function ($file) {
                    return [
                        'Key' => $file->key,
                    ];
                });
                Log::warning('delete directory', ['path' => $path, 'sum' => $list->count()]);
                $this->client->deleteObjects([
                    'Bucket' => $this->bucket,
                    'Objects' => $list,
                ]);
            }
        } catch (ServiceResponseException $e) {
            report($e);
            throw new CosFilesystemException('Delete directory failed: ' . $e->getMessage());
        }
    }

    public function listObjects(string $directory = '', bool $recursive = false): ?ListData
    {
        try {
            $directory = Str::finish($directory, '/');
            $result = $this->client->listObjects([
                'Bucket' => $this->bucket,
                'Delimiter' => $recursive ? '' : '/',
                'MaxKeys' => 1000,
                'Prefix' => $directory,
            ]);
            $data = $result->toArray();
        } catch (ServiceResponseException $e) {
            report($e);

            return null;
        }
        if (!isset($data)) {
            return null;
        }
        try {
            $list = ListData::from($data);
            if ($list->isTruncated) {
                throw new Exception('List is truncated, please use pagination');
            }

            return $list;
        } catch (\Exception $e) {
            Log::error('Error when parsing listObjects response: ' . $e->getMessage());
            report($e);

            return null;
        }

    }

    /**
     * @throws Exception
     */
    public function uploadFile($key, $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }
        $handle = fopen($filePath, 'rb');
        $this->client->upload($this->bucket, $key, $handle);
        if (is_resource($handle)) {
            @fclose($handle);
        }
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
            throw new UnableToCreateDirectory('create directory failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws UnableToWriteFile
     *
     * @see https://cloud.tencent.com/document/product/436/64283#.E7.AE.80.E5.8D.95.E4.B8.8A.E4.BC.A0.E5.AF.B9.E8.B1.A1
     */
    public function uploadData(string $key, mixed $data, StorageClass $class = StorageClass::STANDARD, array $config = []): void
    {
        try {
            $opt = [
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $data,
            ];
            if ($class && $class != StorageClass::STANDARD) {
                $opt['StorageClass'] = $class->value;
            }
            if ($config) {
                $opt = array_merge($opt, $config);
            }
            $this->client->putObject($opt);
        } catch (\Throwable $e) {
            report($e);
            throw new UnableToWriteFile('Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function download($key, $target): void
    {
        try {
            $this->client->download($this->bucket, $key, $target, [
                'PartSize' => 20 * 1024 * 1024, // 分块大小
                'Concurrency' => 1, // 并发数
            ]);
        } catch (\Throwable $e) {
            report($e);
            throw new Exception('Download failed: ' . $e->getMessage());
        }
    }

    public function getData($key)
    {
        try {
            $result = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);

            return $result['Body'];
        } catch (Exception $e) {
            throw UnableToReadFile::fromLocation($key, (string)$e);
        }
    }

    /**
     * @throws Exception
     */
    public function picInfo($key): ImageInfo
    {
        try {
            $result = $this->client->ImageInfo([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);

            return ImageInfo::from($result['Data']);
        } catch (Exception $e) {
            report($e);
            throw new Exception('Get pic info failed: ' . $e->getMessage());
        }
    }

    public function copy($from, $to): void
    {
        $this->client->copy($this->bucket, $to, [
            'Region' => $this->config['region'],
            'Bucket' => $this->bucket,
            'Key' => $from,
        ]);
    }

    protected function buildFullPath(string $key, ?string $version = null): string
    {
        $full = "{$this->bucket}.cos.{$this->config['region']}.myqcloud.com/{$key}";
        if ($version) {
            $full .= '?versionId=' . $version;
        }
        return $full;
    }

    /**
     * @throws CosFilesystemException
     */
    public function setFileAcl(string $key, ObjectAcl $acl): void
    {
        try {
            $this->client->putObjectAcl([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'ACL' => $acl->value,
            ]);
        } catch (Exception $e) {
            throw new CosFilesystemException('setFileAcl failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws CosFilesystemException
     */
    public function getFileAcl(string $key): ObjectAcl
    {

        try {
            $result = $this->client->getObjectAcl([
                'Bucket' => $this->bucket,
                'Key' => $key,
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

    public function setFileAttr($key, FileAttr $attr): void
    {
        $data = [
            'key' => $key,
            'bucket' => $this->bucket,
            'copy_source' => $this->buildFullPath($key),
        ];
        $options = $attr->toArray();
        $this->client->copyObject(array_merge($options, $data));
    }

    public function getFileAttr($key): FileAttr
    {
        $result = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);
        $data = $result->toArray();
        print_r($data);
        return FileAttr::from($data);
    }

    public function fixedUrl(string $key)
    {
        return $this->client->getObjectUrlWithoutSign($this->bucket, $key);
    }

    public function tempUrl(string $key, int $minutes = 10): string
    {
        return $this->client->getObjectUrl($this->bucket, $key, "+{{$minutes}} minutes");
    }
}
