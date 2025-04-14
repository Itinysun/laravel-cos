<?php

namespace Itinysun\LaravelCos;


use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Itinysun\LaravelCos\Data\ImageInfo;
use Itinysun\LaravelCos\Data\ListData;
use Qcloud\Cos\Client;
use Qcloud\Cos\Exception\ServiceResponseException;

readonly class LaravelCos
{
    protected Client $client;
    protected array $config;
    protected string $bucket;

    public function __construct($configName)
    {
        $config = config('laravel-cos.' . $configName);
        $this->client = new Client([
            'region' => $config['region'],
            'schema' => $config['use_https'] ? 'https' : 'http',
            'credentials' => [
                'secretId' => $config['secret_id'],
                'secretKey' => $config['secret_key']
            ]
        ]);
        $this->bucket = $config['bucket'] . '-' . $config['app_id'];
        $this->config = $config;
    }


    public function listObjects(string $directory = '', bool $recursive = false): ?ListData
    {
        try {
            $directory = Str::finish($directory, '/');
            $result = $this->client->listObjects([
                'Bucket' => $this->bucket,
                'Delimiter' => $recursive ? '' : '/',
                'MaxKeys' => 1000,
                'Prefix' => $directory
            ]);
            $data = $result->toArray();
        } catch (ServiceResponseException $e) {
            report($e);
            return null;
        }
        if(!isset($data)){
            return null;
        }
        try {
            $list = ListData::from($data);
            if ($list->isTruncated) {
                throw new Exception('List is truncated, please use pagination');
            }
            return $list;
        }catch (\Exception $e) {
            Log::error('Error when parsing listObjects response: ' . $e->getMessage());
            report($e);
            return null;
        }

    }

    public function exists(string $path): bool
    {
        return $this->client->doesObjectExist($this->bucket, $path);
    }

    /**
     * @throws Exception
     */
    public function delete(string $path): void
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $path
            ]);
        } catch (ServiceResponseException $e) {
            report($e);
            throw new Exception('Delete failed: ' . $e->getMessage());
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
                        'Key' => $file->key
                    ];
                });
                Log::warning('delete directory', ['path' => $path, 'sum' => $list->count()]);
                $this->client->deleteObjects([
                    'Bucket' => $this->bucket,
                    'Objects' => $list
                ]);
            }
        } catch (ServiceResponseException $e) {
            report($e);
            throw new Exception('Delete directory failed: ' . $e->getMessage());
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
    /**
     * @throws Exception
     */
    public function uploadData($key, $data): void
    {
        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $data
            ]);
        } catch (\Throwable $e) {
            report($e);
            throw new Exception('Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function download($key, $target): void
    {
        try {
            $this->client->download($this->bucket, $key, $target, array(
                'PartSize' => 20 * 1024 * 1024, //分块大小
                'Concurrency' => 1, //并发数
            ));
        } catch (\Throwable $e) {
            report($e);
            throw new Exception('Download failed: ' . $e->getMessage());
        }
    }

    public function getData($key)
    {
        $result = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $key
        ]);
        return $result['Body'];
    }

    /**
     * @throws Exception
     */
    public function picInfo($key): ImageInfo
    {
        try {
            $result = $this->client->ImageInfo([
                'Bucket' => $this->bucket,
                'Key' => $key
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
            'Key' => $from
        ]);
    }

    public function fixedUrl(string $key)
    {
        return $this->client->getObjectUrlWithoutSign($this->bucket, $key);
    }

    public function tempUrl(string $key,int $minutes=10): string
    {
        return $this->client->getObjectUrl($this->bucket, $key,"+{{$minutes}} minutes");
    }
}
