<?php

namespace App\Facebook;

use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Symfony\Component\Filesystem\Filesystem;

class PictureUploader
{
    private $facebook;
    private $filesystem;
    private $cacheDir;

    public function __construct(Facebook $facebook, Filesystem $filesystem, string $cacheDir)
    {
        $this->facebook = $facebook;
        $this->filesystem = $filesystem;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @throws FacebookSDKException
     */
    public function upload(string $filteredPictureData)
    {
        $accessToken = $this->facebook->getRedirectLoginHelper()->getAccessToken();
        if (!$accessToken) {
            throw new FacebookSDKException('Empty access token');
        }

        $tempFile = $this->filesystem->tempnam($this->cacheDir, 'fb_profile_pic');
        $this->filesystem->dumpFile($tempFile, $filteredPictureData);

        $accessToken = $accessToken->getValue();
        $parameters = [
            'source' => $this->facebook->fileToUpload($tempFile),
            'message' => '',
        ];

        $response = $this->facebook->post('/me/photos', $parameters, $accessToken)->getDecodedBody();

        $this->filesystem->remove($tempFile);

        return [
            'access_token' => $accessToken,
            'photo_id' => $response['id'],
        ];
    }
}
