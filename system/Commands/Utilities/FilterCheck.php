<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Commands\Utilities\Routes\FilterCollector;
use Config\Services;

/**
 * Check filters for a route.
 */
class FilterCheck extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'CodeIgniter';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'filter:check';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Check filters for a route.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'filter:check <HTTP method> <route>';

    /**
     * the Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'method' => 'The HTTP method. get, post, put, etc.',
        'route'  => 'The route (URI path) to check filtes.',
    ];

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * @return int exit code
     */
    public function run(array $params)
    {
        if (count($params) !== 2) {
            CLI::error('You must specify a HTTP verb and a route.', 'light_gray', 'red');
            CLI::write('  Usage: ' . $this->usage);
            CLI::write('Example: filter:check get /');
            CLI::write('         filter:check put products/1');

            return EXIT_ERROR;
        }

        $method = strtolower($params[0]);
        $route  = $params[1];

        // Load Routes
        $routes = Services::routes();
        require APPPATH . 'Config/Routes.php';
        $routes->getRoutes('*'); // Triggers discovery

        $filterCollector = new FilterCollector();

        $filters = $filterCollector->get($method, $route);

        // PageNotFoundException
        if ($filters['before'] === ['<unknown>']) {
            CLI::error(
                "Can't find a route: " .
                CLI::color(
                    '"' . strtoupper($method) . ' ' . $route . '"',
                    'black'
                ),
            );

            return EXIT_ERROR;
        }

        $tbody[] = [
            strtoupper($method),
            $route,
            implode(' ', $filters['before']),
            implode(' ', $filters['after']),
        ];

        $thead = [
            'Method',
            'Route',
            'Before Filters',
            'After Filters',
        ];

        CLI::table($tbody, $thead);

        return EXIT_SUCCESS;
    }
}
