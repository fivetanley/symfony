<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamedResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $response = new StreamedResponse(function () { echo 'foo'; }, 404, array('Content-Type' => 'text/plain'));

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->headers->get('Content-Type'));
    }

    public function testPrepareWith11Protocol()
    {
        $response = new StreamedResponse(function () { echo 'foo'; });
        $request = Request::create('/');
        $request->server->set('SERVER_PROTOCOL', '1.1');

        $response->prepare($request);

        $this->assertEquals('1.1', $response->getProtocolVersion());
        $this->assertEquals('chunked', $response->headers->get('Transfer-Encoding'));
        $this->assertEquals('no-cache, private', $response->headers->get('Cache-Control'));
    }

    public function testPrepareWith10Protocol()
    {
        $response = new StreamedResponse(function () { echo 'foo'; });
        $request = Request::create('/');
        $request->server->set('SERVER_PROTOCOL', '1.0');

        $response->prepare($request);

        $this->assertEquals('1.0', $response->getProtocolVersion());
        $this->assertNull($response->headers->get('Transfer-Encoding'));
        $this->assertEquals('no-cache, private', $response->headers->get('Cache-Control'));
    }

    public function testSendContent()
    {
        $called = 0;

        $response = new StreamedResponse(function () use (&$called) { ++$called; });

        $response->sendContent();
        $this->assertEquals(1, $called);

        $response->sendContent();
        $this->assertEquals(1, $called);
    }

    /**
     * @expectedException \LogicException
     */
    public function testSendContentWithNonCallable()
    {
        $response = new StreamedResponse('foobar');
        $response->sendContent();
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetContent()
    {
        $response = new StreamedResponse(function () { echo 'foo'; });
        $response->setContent('foo');
    }

    public function testGetContent()
    {
        $response = new StreamedResponse(function () { echo 'foo'; });
        $this->assertFalse($response->getContent());
    }
}
