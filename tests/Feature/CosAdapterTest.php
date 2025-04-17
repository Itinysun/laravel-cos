<?php
beforeEach(function () {

})->skip(fn() => empty(config('cos.default')), 'cos_config.php is empty');

describe('test flysystem adapter', function () {
    $testFile = [
        'key' => 'test/test1.txt',
    ];
    it('can write file', function ($key) {
        \Illuminate\Support\Facades\Storage::disk('cos')->put($key, 'test');
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('cos')->exists($key));
    })->with($testFile);
    it('can read file', function ($key) {
        $data = \Illuminate\Support\Facades\Storage::disk('cos')->get($key);
        $this->assertEquals($data, 'test');
    })->with($testFile);
    it('can delete file', function ($key) {
        \Illuminate\Support\Facades\Storage::disk('cos')->delete($key);
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('cos')->exists($key));
    })->with($testFile);

    it('can write stream', function ($key) {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, 'test');
        rewind($stream);
        \Illuminate\Support\Facades\Storage::disk('cos')->writeStream($key, $stream);
        fclose($stream);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('cos')->exists($key));
    })->with($testFile);

    it('can read stream', function ($key) {
        $stream = \Illuminate\Support\Facades\Storage::disk('cos')->readStream($key);
        $data = stream_get_contents($stream);
        fclose($stream);
        $this->assertEquals($data, 'test');
    })->with($testFile);

    it('can create directory', function ($key) {
        \Illuminate\Support\Facades\Storage::disk('cos')->makeDirectory($key);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('cos')->directoryExists($key));
    })->with($testFile);

    it('can delete directory', function ($key) {
        \Illuminate\Support\Facades\Storage::disk('cos')->deleteDirectory($key);
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('cos')->directoryExists($key));
    })->with($testFile);
});
