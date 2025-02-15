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

namespace CodeIgniter\Database\Migrations;

use PHPUnit\Framework\Attributes\Group;
use CodeIgniter\Database\Migration;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class MigrationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDBGroup(): void
    {
        $migration = new class () extends Migration {
            protected $DBGroup = 'tests';

            public function up(): void
            {
            }

            public function down(): void
            {
            }
        };

        $dbGroup = $migration->getDBGroup();

        $this->assertSame('tests', $dbGroup);
    }
}
