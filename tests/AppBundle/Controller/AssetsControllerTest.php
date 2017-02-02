<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AssetsControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testStaticMaps()
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/maps/47.395081,8.336142');
        $response = $client->getResponse();

        $this->assertResponseStatusCode(Response::HTTP_OK, $response);

        $weight = strlen($response->getContent());
        $this->assertGreaterThan(1024, $weight, 'We are assuming that an image map should be greater than 1 KB');
        $this->assertLessThan(1024 * 1024, $weight, 'We are assuming that an image map should be less than 1 MB');

        $this->assertSame('image/png', $response->headers->get('content-type'));

        $tag = md5($response->getContent());

        $client->request(Request::METHOD_GET, '/maps/47.395081,8.336142');
        $this->assertEquals(
            $tag,
            md5($client->getResponse()->getContent()),
            'Exactly same queries should have exactly same image data responses'
        );

        $client->request(Request::METHOD_GET, '/maps/47.395081,9.336142');
        $this->assertNotEquals(
            $tag,
            md5($client->getResponse()->getContent()),
            'Different queries should not have exactly same image data responses'
        );

        $client->request(Request::METHOD_GET, '/maps/-47.395081,-8.336142');
        $this->assertResponseStatusCode(
            Response::HTTP_OK,
            $client->getResponse(),
            'Negative values are allowed'
        );

        $client->request(Request::METHOD_GET, '/maps/-47.39508,-8.3361');
        $this->assertResponseStatusCode(
            Response::HTTP_OK,
            $client->getResponse(),
            'Precision of various digits count is allowed'
        );

        $client->request(Request::METHOD_GET, '/maps/-47,-8');
        $this->assertResponseStatusCode(
            Response::HTTP_OK,
            $client->getResponse(),
            'Integer values are allowed'
        );
    }

    public function testStaticMapsWithWrongQuery()
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/maps/47.395081');
        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $client->getResponse(),
            'Query should have complete coordinates parameters'
        );

        $client->request(Request::METHOD_GET, '/maps/47.,8.');
        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $client->getResponse(),
            'Coordinates should have a precision of at least 1 digit'
        );

        $client->request(Request::METHOD_GET, '/maps/47.3950834,8.3361424');
        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $client->getResponse(),
            'Coordinates should have a precision of max 6 digits'
        );

        $client->request(Request::METHOD_GET, '/maps/47.395081;8.336142');
        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $client->getResponse(),
            'Coordinates separator should be a comma'
        );

        $client->request(Request::METHOD_GET, '/maps/47,395081,8,336142');
        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $client->getResponse(),
            'Precision separator should be a dot'
        );

        $client->request(Request::METHOD_GET, '/maps/47.39508a,8.336142');
        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $client->getResponse(),
            'Foreign characters are not allowed'
        );
    }
}
