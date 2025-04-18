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
    it('can read and write file', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $str = \Illuminate\Support\Str::random();
        $laravelCos->uploadData($key, $str);
        $data = $laravelCos->getData($key);
        $this->assertEquals($str, $data);
    })->with($testFile);


    it('can download file', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
                $str = \Illuminate\Support\Str::random();
        $laravelCos->uploadData($key, $str);
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


    it('get acl success', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $acl = $laravelCos->getFileAcl($key);
        $this->assertEquals($acl, \Itinysun\LaravelCos\Enums\ObjectAcl::PRIVATE);
    })->with($testFile);

    it('get attr success', function ($key) {
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

    it('can set file acl', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $laravelCos->setFileAcl($key, \Itinysun\LaravelCos\Enums\ObjectAcl::PUBLIC_READ);
        $acl = $laravelCos->getFileAcl($key);
        $this->assertEquals($acl, \Itinysun\LaravelCos\Enums\ObjectAcl::PUBLIC_READ);
    })->with($testFile);

    it('can set file attr', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();

        $laravelCos->uploadData($key, 'test');

        $attr = new \Itinysun\LaravelCos\Data\FileCopyAttr();
        $attr->acl = \Itinysun\LaravelCos\Enums\ObjectAcl::PRIVATE;
        $str = \Illuminate\Support\Str::random();

        $attr->metadata = [
            'key'=>$str
        ];

        $laravelCos->setFileAttr($key, $attr);

        $newAttr=$laravelCos->getFileAttr($key);

        $laravelCos->delete($key);

        $this->assertEquals($newAttr->metadata['key'], $str);
    })->with($testFile);

    it('can copy file', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $str = \Illuminate\Support\Str::random();
        $laravelCos->uploadData($key, $str);
        $newKey = 'test/test3.txt';
        $laravelCos->copy($key, $newKey);
        $data = $laravelCos->getData($newKey);
        $laravelCos->delete($newKey);
        $this->assertEquals($str, $data);
    })->with($testFile);

    it('can move file', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $str = \Illuminate\Support\Str::random();
        $laravelCos->uploadData($key, $str);
        $newKey = 'test/test3.txt';
        $laravelCos->move($key, $newKey);
        $data = $laravelCos->getData($newKey);
        $this->assertEquals($str, $data);
    })->with($testFile);

    it('can get temp url', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $str = \Illuminate\Support\Str::random();
        $laravelCos->uploadData($key, $str);
        $url = $laravelCos->tempUrl($key, \Illuminate\Support\Carbon::now()->minutes(30),['foo'=>'bar']);
        $hasParam = \Illuminate\Support\Str::contains($url, 'foo=bar');
        $this->assertTrue($hasParam);
    })->with($testFile);
    it('can get fixed url', function ($key) {
        $laravelCos = new Itinysun\LaravelCos\LaravelCos();
        $str = \Illuminate\Support\Str::random();
        $laravelCos->uploadData($key, $str);
        $url = $laravelCos->fixedUrl($key, ['foo'=>'bar']);
        $hasParam = \Illuminate\Support\Str::contains($url, 'foo=bar');
        $this->assertTrue($hasParam);
    })->with($testFile);
});
