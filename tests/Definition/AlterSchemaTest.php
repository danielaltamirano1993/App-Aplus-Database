<?php
/*
 * This file is part of Aplus Framework Database Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Database\Definition;

use Framework\Database\Definition\AlterSchema;
use Tests\Database\TestCase;

final class AlterSchemaTest extends TestCase
{
    protected AlterSchema $alterSchema;

    protected function setUp() : void
    {
        $this->alterSchema = new AlterSchema(static::$database);
    }

    public function testSchemaWithoutSpecification() : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('ALTER SCHEMA must have a specification');
        $this->alterSchema->schema('app')->sql();
    }

    public function testEmptySchema() : void
    {
        self::assertSame(
            "ALTER SCHEMA\n CHARACTER SET = 'utf8'\n",
            $this->alterSchema->charset('utf8')->sql()
        );
    }

    public function testCharset() : void
    {
        self::assertSame(
            "ALTER SCHEMA `app`\n CHARACTER SET = 'utf8'\n",
            $this->alterSchema->schema('app')->charset('utf8')->sql()
        );
    }

    public function testCollate() : void
    {
        self::assertSame(
            "ALTER SCHEMA `app`\n COLLATE = 'utf8_general_ci'\n",
            $this->alterSchema->schema('app')->collate('utf8_general_ci')->sql()
        );
    }

    public function testUpgrade() : void
    {
        self::assertSame(
            "ALTER SCHEMA `#mysql50#app`\n UPGRADE DATA DIRECTORY NAME\n",
            $this->alterSchema->schema('app')->upgrade()->sql()
        );
    }

    public function testUpgradeConflict() : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'UPGRADE DATA DIRECTORY NAME can not be used with CHARACTER SET or COLLATE'
        );
        $this->alterSchema->schema('app')->upgrade()->charset('utf8')->sql();
    }

    public function testFullSql() : void
    {
        self::assertSame(
            "ALTER SCHEMA `app`\n CHARACTER SET = 'utf8'\n COLLATE = 'utf8_general_ci'\n",
            $this->alterSchema
                ->schema('app')
                ->charset('utf8')
                ->collate('utf8_general_ci')
                ->sql()
        );
    }

    public function testRun() : void
    {
        static::$database->dropSchema()->schema('app')->ifExists()->run();
        static::$database->createSchema()->schema('app')->charset('utf8')->run();
        self::assertSame(
            1,
            $this->alterSchema->schema('app')->charset('utf8mb4')->run()
        );
    }
}
