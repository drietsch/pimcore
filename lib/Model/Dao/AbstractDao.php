<?php
declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace Pimcore\Model\Dao;

use Pimcore\Guardian\Client;
use Pimcore\Guardian\NotOnGuardianYet;

abstract class AbstractDao implements DaoInterface
{
    use DaoTrait;

    /**
     * The kernel's door to Guardian (guardian-kernel): what a Dao persists
     * through instead of a database connection.
     */
    protected Client $guardian;

    public function configure(): void
    {
        $this->guardian = Client::get();
    }

    /**
     * A Dao that still reaches for `$this->db` has not been rewritten onto
     * Guardian: refuse by name rather than fail on an unknown property.
     */
    public function __get(string $name): mixed
    {
        if ($name === 'db') {
            throw NotOnGuardianYet::for(static::class, 'M2', 'this Dao still expects a SQL connection ($this->db)');
        }

        throw new \Error(sprintf('Undefined property: %s::$%s', static::class, $name));
    }

    public function beginTransaction(): void
    {
        // A unit of work on Guardian is one commit (guardian-runner ADR-0013);
        // the Daos that batch writes do so through Pimcore\Guardian\UnitOfWork.
    }

    public function commit(): void
    {
    }

    public function rollBack(): void
    {
    }

    /**
     * @return string[]
     */
    public function getPrimaryKey(string $table, bool $cache = true): array
    {
        throw NotOnGuardianYet::for(static::class . '::getPrimaryKey', 'M2', 'table ' . $table);
    }

    /**
     * @return string[]
     */
    public function getValidTableColumns(string $table, bool $cache = true, bool $primaryKeyColumnsOnly = false): array
    {
        throw NotOnGuardianYet::for(static::class . '::getValidTableColumns', 'M2', 'table ' . $table);
    }

    public function resetValidTableColumnsCache(string $table): void
    {
    }

    public static function getForeignKeyName(string $table, string $column): string
    {
        $fkName = 'fk_'.$table.'__'.$column;
        if (strlen($fkName) > 64) {
            $fkName = substr($fkName, 0, 55) . '_' . hash('crc32', $fkName);
        }

        return $fkName;
    }
}
