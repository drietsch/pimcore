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

namespace Pimcore;

use Pimcore\Guardian\NotOnGuardianYet;

/**
 * There is no SQL database in this kernel (guardian-kernel, guardian-runner
 * ADR-0013): persistence is Guardian's. This class stays so that every place
 * that still asks for a connection refuses by name — the caller shows in the
 * refusal — instead of failing on a missing class.
 */
class Db
{
    public static function getConnection(): never
    {
        throw NotOnGuardianYet::sql('Db::getConnection()');
    }

    public static function reset(): never
    {
        throw NotOnGuardianYet::sql('Db::reset()');
    }

    public static function get(): never
    {
        throw NotOnGuardianYet::sql('Db::get()');
    }

    public static function close(): void
    {
    }
}
