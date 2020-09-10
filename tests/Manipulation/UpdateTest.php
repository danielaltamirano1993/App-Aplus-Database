<?php
/*
 * This file is part of Aplus Framework Database Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Database\Manipulation;

use Framework\Database\Manipulation\Update;
use Tests\Database\TestCase;

final class UpdateTest extends TestCase
{
    protected Update $update;

    public function setup() : void
    {
        $this->update = new Update(static::$database);
    }

    protected function prepare() : void
    {
        $this->update->table('t1');
    }

    public function testRenderWithoutTable() : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Table references must be set');
        $this->update->sql();
    }

    public function testRenderWithoutSet() : void
    {
        $this->prepare();
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('SET statement must be set');
        $this->update->sql();
    }

    public function testOptions() : void
    {
        $this->update->table('t1')->set(['id' => 1]);
        $this->update->options($this->update::OPT_LOW_PRIORITY);
        self::assertSame(
            "UPDATE\n LOW_PRIORITY\n `t1`\n SET `id` = 1\n",
            $this->update->sql()
        );
        $this->update->options($this->update::OPT_IGNORE);
        self::assertSame(
            "UPDATE\n IGNORE\n `t1`\n SET `id` = 1\n",
            $this->update->sql()
        );
        $this->update->options($this->update::OPT_LOW_PRIORITY, $this->update::OPT_IGNORE);
        self::assertSame(
            "UPDATE\n LOW_PRIORITY IGNORE\n `t1`\n SET `id` = 1\n",
            $this->update->sql()
        );
    }

    public function testInvalidOption() : void
    {
        $this->prepare();
        $this->update->set(['id' => 1]);
        $this->update->options('foo');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option: foo');
        $this->update->sql();
    }

    public function testJoin() : void
    {
        $this->update->table('t1', 't2')
            ->innerJoinOn('t2', static fn () => 't1.c1 = t2.c1')
            ->set([
                't1.c1' => static fn () => 't2.c2',
            ]);
        self::assertSame(
            "UPDATE\n `t1`, `t2`\n INNER JOIN `t2` ON (t1.c1 = t2.c1)\n SET `t1`.`c1` = (t2.c2)\n",
            $this->update->sql()
        );
    }

    public function testLimit() : void
    {
        $this->update->table('t1')->set(['id' => 1]);
        $this->update->limit(1);
        self::assertSame(
            "UPDATE\n `t1`\n SET `id` = 1\n LIMIT 1\n",
            $this->update->sql()
        );
        $this->update->limit('235'); // @phpstan-ignore-line
        self::assertSame(
            "UPDATE\n `t1`\n SET `id` = 1\n LIMIT 235\n",
            $this->update->sql()
        );
    }

    public function testWhere() : void
    {
        $this->update->table('t1')->set(['name' => 'Foo']);
        $this->update->whereEqual('id', 1);
        self::assertSame(
            "UPDATE\n `t1`\n SET `name` = 'Foo'\n WHERE `id` = 1\n",
            $this->update->sql()
        );
    }

    public function testOrderBy() : void
    {
        $this->update->table('t1')->set(['name' => 'Foo']);
        $this->update->orderByAsc('id');
        self::assertSame(
            "UPDATE\n `t1`\n SET `name` = 'Foo'\n ORDER BY `id` ASC\n",
            $this->update->sql()
        );
    }

    public function testRun() : void
    {
        $this->createDummyData();
        self::assertSame(
            3,
            $this->update->table('t1')->set(['c2' => 'x'])->whereIn('c1', 1, 2, 3)->run()
        );
    }
}
