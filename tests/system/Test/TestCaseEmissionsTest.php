<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use CodeIgniter\HTTP\Response;
use Config\App;

/**
 * This test suite has been created separately from
 * TestCaseTest because it messes with output
 * buffering from PHPUnit, and the individual
 * test cases need to be run as separate processes.
 *
 * @internal
 */
#[Group('SeparateProcess')]
final class TestCaseEmissionsTest extends CIUnitTestCase
{
    /**
     * These need to be run as a separate process, since phpunit
     * has already captured the "normal" output, and we will get
     * a "Cannot modify headers" message if we try to change
     * headers or cookies now.
     *
     * Furthermore, these tests needs to flush the output buffering
     * that might be in progress, and start our own output buffer
     * capture.
     *
     * The tests includes a basic sanity check, to make sure that
     * the body we thought would be sent actually was.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testHeadersEmitted(): void
    {
        // Workaround for errors on PHPUnit 10 and PHP 8.3.
        // See https://github.com/sebastianbergmann/phpunit/issues/5403#issuecomment-1906810619
        restore_error_handler();

        $response = new Response(new App());
        $response->pretend(false);

        $body = 'Hello';
        $response->setBody($body);

        $response->setCookie('foo', 'bar');
        $this->assertTrue($response->hasCookie('foo'));
        $this->assertTrue($response->hasCookie('foo', 'bar'));

        // send it
        ob_start();
        $response->send();
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // and what actually got sent?; test both ways
        $this->assertHeaderEmitted('Set-Cookie: foo=bar;');
        $this->assertHeaderEmitted('set-cookie: FOO=bar', true);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testHeadersNotEmitted(): void
    {
        // Workaround for errors on PHPUnit 10 and PHP 8.3.
        // See https://github.com/sebastianbergmann/phpunit/issues/5403#issuecomment-1906810619
        restore_error_handler();

        $response = new Response(new App());
        $response->pretend(false);

        $body = 'Hello';
        $response->setBody($body);

        // what do we think we're about to send?
        $response->setCookie('foo', 'bar');
        $this->assertTrue($response->hasCookie('foo'));
        $this->assertTrue($response->hasCookie('foo', 'bar'));

        // send it
        ob_start();
        $response->send(); // what really was sent
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $this->assertHeaderNotEmitted('Set-Cookie: pop=corn', true);
    }
}
