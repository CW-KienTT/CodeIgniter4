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

namespace CodeIgniter\Database\Builder;

use PHPUnit\Framework\Attributes\Group;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
#[Group('Others')]
final class TruncateTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testTruncate(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $expectedSQL = 'TRUNCATE "user"';

        $this->assertSame($expectedSQL, $builder->testMode()->truncate());
    }
}
