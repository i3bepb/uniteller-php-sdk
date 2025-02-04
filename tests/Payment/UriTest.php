<?php

namespace Tmconsulting\Uniteller\Tests\Payment;

use Tmconsulting\Uniteller\Payment\Uri;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Payment\Uri
 */
class UriTest extends TestCase
{
    public function testGetUriReturnsCorrectValue()
    {
        $expectedUri = 'https://example.com/payment';
        $uri = new Uri($expectedUri);

        $this->assertSame($expectedUri, $uri->getUri());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGoSendsHeader()
    {
        $uriString = 'https://example.com/redirect';
        $uri = new Uri($uriString);

        // Включаем output buffering, чтобы поймать header
        ob_start();
        $uri->go();
        $headers = xdebug_get_headers();
        ob_end_clean();

        $this->assertContains("Location: $uriString", $headers);
    }
}