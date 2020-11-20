<?php

namespace App\Storage;

use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter as MainGoogleStorageAdapter;

class GoogleStorageAdapter extends MainGoogleStorageAdapter implements UrlAdapterInterface
{
}
