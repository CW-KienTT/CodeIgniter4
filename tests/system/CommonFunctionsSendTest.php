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

namespace CodeIgniter;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
#[Group('SeparateProcess')]
final class CommonFunctionsSendTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        unset($_ENV['foo'], $_SERVER['foo']);

        // Workaround for errors on PHPUnit 10 and PHP 8.3.
        // See https://github.com/sebastianbergmann/phpunit/issues/5403#issuecomment-1906810619
        restore_error_handler();
    }

    /**
     * Make sure cookies are set by RedirectResponse this way
     * See https://github.com/codeigniter4/CodeIgniter4/issues/1393
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testRedirectResponseCookiesSent(): void
    {
        $loginTime = time();

        $routes = service('routes');
        $routes->add('user/login', 'Auth::verify', ['as' => 'login']);

        $response = redirect()->route('login')
            ->setCookie('foo', 'onething', YEAR)
            ->setCookie('login_time', (string) $loginTime, YEAR);
        $response->pretend(false);
        $this->assertTrue($response->hasCookie('foo', 'onething'));
        $this->assertTrue($response->hasCookie('login_time'));
        $response->setBody('Hello');

        // send it
        ob_start();
        $response->send();
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // and what actually got sent?
        $this->assertHeaderEmitted('Set-Cookie: foo=onething;');
        $this->assertHeaderEmitted('Set-Cookie: login_time');
    }
}
