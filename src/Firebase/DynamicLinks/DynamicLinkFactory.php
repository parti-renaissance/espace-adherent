<?php

namespace App\Firebase\DynamicLinks;

use Kreait\Firebase\DynamicLink\AndroidInfo;
use Kreait\Firebase\DynamicLink\CreateDynamicLink;
use Kreait\Firebase\DynamicLink\IOSInfo;
use Kreait\Firebase\DynamicLink\NavigationInfo;
use Kreait\Firebase\DynamicLink\SocialMetaTagInfo;

class DynamicLinkFactory
{
    private string $dynamicLinksHost;
    private string $appPackageId;

    public function __construct(string $dynamicLinksHost, string $jemengageAppPackageId)
    {
        $this->dynamicLinksHost = $dynamicLinksHost;
        $this->appPackageId = $jemengageAppPackageId;
    }

    public function create(DynamicLinkObjectInterface $object): CreateDynamicLink
    {
        if (!str_starts_with($url = $object->getDynamicLinkPath(), 'http')) {
            $url = \sprintf('%s/%s', rtrim($this->dynamicLinksHost, '/'), ltrim($url, '/'));
        }

        $link = CreateDynamicLink::forUrl($url)
            ->withNavigationInfo(NavigationInfo::new()->withForcedRedirect())
            ->withAndroidInfo(AndroidInfo::new()->withPackageName($this->appPackageId))
            ->withIOSInfo(IOSInfo::new()->withBundleId($this->appPackageId))
        ;

        if ($object->withSocialMeta()) {
            $link = $link->withSocialMetaTagInfo(
                SocialMetaTagInfo::new()
                    ->withTitle($object->getSocialTitle())
                    ->withDescription($object->getSocialDescription())
            );
        }

        return $link;
    }
}
