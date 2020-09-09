<?php
/*
 * This file is part of Aplus Framework Database Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Database\Definition\Table\Columns;

use Framework\Database\Definition\Table\Columns\Column;

class ColumnMock extends Column
{
    public string $type = 'mock';
}
