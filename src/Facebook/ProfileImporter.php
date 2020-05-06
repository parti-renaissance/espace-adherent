<?php

namespace App\Facebook;

use App\Entity\FacebookProfile;
use App\Repository\FacebookProfileRepository;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;

class ProfileImporter
{
    private $facebook;
    private $repository;

    public function __construct(Facebook $facebook, FacebookProfileRepository $repository)
    {
        $this->facebook = $facebook;
        $this->repository = $repository;
    }

    /**
     * @throws FacebookSDKException
     */
    public function import(): FacebookProfile
    {
        $accessToken = $this->facebook->getRedirectLoginHelper()->getAccessToken();
        if (!$accessToken) {
            throw new FacebookSDKException('Empty access token');
        }

        $accessToken = $accessToken->getValue();
        $response = $this->facebook->get('/me?fields=id,email,name,age_range,gender', $accessToken)->getDecodedBody();

        return $this->repository->persistFromSDKResponse($accessToken, $response);
    }
}
