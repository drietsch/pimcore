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

namespace Pimcore\Db;

use Pimcore\Guardian\NotOnGuardianYet;

/**
 * The SQL helpers (`upsert`, `quoteInto`, `escapeLike`, …) are gone with the
 * database (guardian-kernel). Any remaining static call refuses by name.
 */
class Helper
{
    /**
     * @param array<int, mixed> $arguments
     */
    public static function __callStatic(string $name, array $arguments): never
    {
        throw NotOnGuardianYet::sql('Db\\Helper::' . $name . '()');
    }
}
