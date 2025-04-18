<?php

namespace Itinysun\LaravelCos\Exceptions;

use League\Flysystem\FilesystemException;

class CosFilesystemException extends \Exception implements FilesystemException {}
