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

namespace CodeIgniter\Commands\Utilities\Routes\AutoRouterImproved;

use PHPUnit\Framework\Attributes\Group;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Filters;
use Config\Services;

/**
 * @internal
 */
#[Group('Others')]
final class AutoRouteCollectorTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices(true);
    }

    private function createAutoRouteCollector(array $filterConfigFilters): AutoRouteCollector
    {
        $routes = Services::routes();
        $routes->resetRoutes();
        $routes->setAutoRoute(true);
        config('Feature')->autoRoutesImproved = true;
        $namespace                            = 'Tests\Support\Controllers';
        $routes->setDefaultNamespace($namespace);

        /** @var Filters $filterConfig */
        $filterConfig          = config('Filters');
        $filterConfig->filters = $filterConfigFilters;
        Services::filters($filterConfig);

        return new AutoRouteCollector(
            $namespace,
            'Home',
            'index',
            [Method::GET, Method::POST],
            [],
        );
    }

    public function testGetFilterMatches(): void
    {
        $filterConfigFilters = ['honeypot' => ['before' => ['newautorouting/save*']]];
        $collector           = $this->createAutoRouteCollector($filterConfigFilters);

        $routes = $collector->get();

        $expected = [
            0 => [
                'GET(auto)',
                'newautorouting[/..]',
                '',
                '\\Tests\\Support\\Controllers\\Newautorouting::getIndex',
                '',
                '',
            ],
            1 => [
                'POST(auto)',
                'newautorouting/save/../..[/..]',
                '',
                '\\Tests\\Support\\Controllers\\Newautorouting::postSave',
                'honeypot',
                '',
            ],
        ];
        $this->assertSame($expected, $routes);
    }

    public function testGetFilterDoesNotMatch(): void
    {
        $filterConfigFilters = ['honeypot' => ['before' => ['newautorouting/save/*/*']]];
        $collector           = $this->createAutoRouteCollector($filterConfigFilters);

        $routes = $collector->get();

        $expected = [
            0 => [
                'GET(auto)',
                'newautorouting[/..]',
                '',
                '\\Tests\\Support\\Controllers\\Newautorouting::getIndex',
                '',
                '',
            ],
            1 => [
                'POST(auto)',
                'newautorouting/save/../..[/..]',
                '',
                '\\Tests\\Support\\Controllers\\Newautorouting::postSave',
                '',
                '',
            ],
        ];
        $this->assertSame($expected, $routes);
    }
}
