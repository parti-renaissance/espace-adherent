<?php

namespace App\Tests\Security;

use AppBundle\Security\LogoutSuccessHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class LogoutSuccessHandlerTest extends TestCase
{
    public function testItRedirectWithARedirectUri()
    {
        $request = $this->getMockBuilder(Request::class)->getMock();
        $request->expects($this->once())->method('getSchemeAndHttpHost')->willReturn('http://foo');

        $response = (new LogoutSuccessHandler('http://bar'))->onLogoutSuccess($request);

        $this->assertSame('http://bar?redirect_uri=http://foo', $response->getTargetUrl());
    }
}
