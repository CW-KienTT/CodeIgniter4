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
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class EmptyTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testEmpty(): void
    {
        $this->db->table('misc')->emptyTable();

        $this->assertSame(0, $this->db->table('misc')->countAll());
    }

    public function testTruncate(): void
    {
        $this->db->table('misc')->truncate();

        $this->assertSame(0, $this->db->table('misc')->countAll());
    }
}
