<?php
beforeEach(function () {

})->skip(fn() => empty(config('cos.default')), 'cos_config.php is empty');

describe('test cos sdk', function () {
    $testFile = [
        'key' => 'test/test2.txt',
    ];
    $testDir = [
        'test/test2',
    ];
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
        $laravelCos->download($key, './test2.txt');
        $this->assertFileExists('./test2.txt');
        unlink('./test2.txt');
    })->with($testFile);

    it('can delete file', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $laravelCos->uploadData($key, 'test');
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

    it('can delete directory', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $laravelCos->uploadData($key . '/test.txt', 'test');
        $laravelCos->uploadData($key . '/test2.txt', 'test');
        $laravelCos->deleteDirectory($key);
        $this->assertFalse($laravelCos->directoryExists($key));
    })->with($testDir);
});
