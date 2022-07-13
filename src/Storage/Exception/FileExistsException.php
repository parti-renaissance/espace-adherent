<?php

namespace App\Storage\Exception;

use League\Flysystem\FileExistsException as BaseFileExistsException;

class FileExistsException extends BaseFileExistsException
{
}
