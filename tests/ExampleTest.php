<?php

beforeEach(function () {

})->skip(fn() => empty(config('cos.default')), 'cos_config.php is empty');

$testFile = [
    'key' => 'test/test.txt',
];

describe('test cos sdk', function () use ($testFile) {
    it('can write file', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $laravelCos->uploadData($key, 'test');
        $this->assertTrue($laravelCos->exists($key));
    })->with($testFile);

    it('can read file', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $data = $laravelCos->getData($key);
        $this->assertEquals($data, 'test');
    })->with($testFile);

    it('can download file', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $result = $laravelCos->download($key, './test.txt');
        $this->assertFileExists('./test.txt');
    })->with($testFile);

    it('can delete file', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $laravelCos->delete($key);
        $this->assertFalse($laravelCos->exists($key));
    })->with($testFile);

    it('can upload file', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $laravelCos->uploadFile($key, './test.txt');
        $this->assertTrue($laravelCos->exists($key));
    })->with($testFile);


    it('test acl success', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $acl = $laravelCos->getFileAcl($key);
        $this->assertEquals($acl, \Itinysun\LaravelCos\Enums\ObjectAcl::PRIVATE);
    })->with($testFile);

    it('test attr success', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $attr = $laravelCos->getFileAttr($key);
        $this->assertEquals($attr->key, $key);
    })->with($testFile);
});

describe('test flysystem adapter',function () use ($testFile){
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
});
