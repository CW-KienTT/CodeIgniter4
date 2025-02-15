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

namespace CodeIgniter\Database\Live;

use PHPUnit\Framework\Attributes\Group;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class GetVersionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;

    public function testGetVersion(): void
    {
        $version = $this->db->getVersion();

        $this->assertMatchesRegularExpression('/\A\d+(\.\d+)*\z/', $version);
    }
}
