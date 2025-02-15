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

namespace CodeIgniter\HTTP;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class SiteURIFactoryDetectRoutePathTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_GET = $_SERVER = [];
    }

    private function createSiteURIFactory(array $server, ?App $appConfig = null): SiteURIFactory
    {
        $appConfig ??= new App();

        $_SERVER      = $server;
        $superglobals = new Superglobals();

        return new SiteURIFactory($appConfig, $superglobals);
    }

    public function testDefault(): void
    {
        // /index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath());
    }

    public function testDefaultEmpty(): void
    {
        // /
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = '/';
        $this->assertSame($expected, $factory->detectRoutePath());
    }

    public function testRequestURI(): void
    {
        // /index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINested(): void
    {
        // I'm not sure but this is a case of Apache config making such SERVER
        // values?
        // The current implementation doesn't use the value of the URI object.
        // So I removed the code to set URI. Therefore, it's exactly the same as
        // the method above as a test.
        // But it may be changed in the future to use the value of the URI object.
        // So I don't remove this test case.

        // /ci/index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURISubfolder(): void
    {
        // /ci/index.php/popcorn/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/ci/index.php/popcorn/woot';
        $_SERVER['SCRIPT_NAME'] = '/ci/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'popcorn/woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINoIndex(): void
    {
        // /sub/example
        $_SERVER['REQUEST_URI'] = '/sub/example';
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'example';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINginx(): void
    {
        // /ci/index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINginxRedirecting(): void
    {
        // /?/ci/index.php/woot
        $_SERVER['REQUEST_URI'] = '/?/ci/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURISuppressed(): void
    {
        // /woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/woot';
        $_SERVER['SCRIPT_NAME'] = '/';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIGetPath(): void
    {
        // /index.php/fruits/banana
        $_SERVER['REQUEST_URI'] = '/index.php/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIPathIsRelative(): void
    {
        // /sub/folder/index.php/fruits/banana
        $_SERVER['REQUEST_URI'] = '/sub/folder/index.php/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/sub/folder/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIStoresDetectedPath(): void
    {
        // /fruits/banana
        $_SERVER['REQUEST_URI'] = '/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $_SERVER['REQUEST_URI'] = '/candy/snickers';

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIPathIsNeverRediscovered(): void
    {
        $_SERVER['REQUEST_URI'] = '/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $_SERVER['REQUEST_URI'] = '/candy/snickers';
        $factory->detectRoutePath('REQUEST_URI');

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testQueryString(): void
    {
        // /index.php?/ci/woot
        $_SERVER['REQUEST_URI']  = '/index.php?/ci/woot';
        $_SERVER['QUERY_STRING'] = '/ci/woot';
        $_SERVER['SCRIPT_NAME']  = '/index.php';

        $_GET['/ci/woot'] = '';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
    }

    public function testQueryStringWithQueryString(): void
    {
        // /index.php?/ci/woot?code=good#pos
        $_SERVER['REQUEST_URI']  = '/index.php?/ci/woot?code=good';
        $_SERVER['QUERY_STRING'] = '/ci/woot?code=good';
        $_SERVER['SCRIPT_NAME']  = '/index.php';

        $_GET['/ci/woot?code'] = 'good';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
        $this->assertSame('code=good', $_SERVER['QUERY_STRING']);
        $this->assertSame(['code' => 'good'], $_GET);
    }

    public function testQueryStringEmpty(): void
    {
        // /index.php?
        $_SERVER['REQUEST_URI'] = '/index.php?';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = '/';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
    }

    public function testPathInfoUnset(): void
    {
        // /index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('PATH_INFO'));
    }

    public function testPathInfoSubfolder(): void
    {
        $appConfig          = new App();
        $appConfig->baseURL = 'http://localhost:8888/ci431/public/';

        // http://localhost:8888/ci431/public/index.php/woot?code=good#pos
        $_SERVER['PATH_INFO']   = '/woot';
        $_SERVER['REQUEST_URI'] = '/ci431/public/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME'] = '/ci431/public/index.php';

        $factory = $this->createSiteURIFactory($_SERVER, $appConfig);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('PATH_INFO'));
    }

    /**
     * @param string $path
     * @param string $detectPath
     */
    #[DataProvider('provideExtensionPHP')]
    public function testExtensionPHP($path, $detectPath): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $_SERVER['REQUEST_URI'] = $path;
        $_SERVER['SCRIPT_NAME'] = $path;

        $factory = $this->createSiteURIFactory($_SERVER, $config);

        $this->assertSame($detectPath, $factory->detectRoutePath());
    }

    public static function provideExtensionPHP(): iterable
    {
        return [
            'not /index.php' => [
                '/test.php',
                '/',
            ],
            '/index.php' => [
                '/index.php',
                '/',
            ],
        ];
    }
}
