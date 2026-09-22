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

namespace Pimcore\Guardian;

use RuntimeException;

/**
 * A subsystem of this kernel that has not been rewritten onto Guardian yet.
 *
 * The guardian-kernel branch replaces Pimcore's persistence — MySQL through
 * Doctrine DBAL, OpenSearch, Doctrine Messenger — with Guardian's capabilities
 * (guardian-runner ADR-0013). Nothing is emulated on the way: a subsystem is
 * either on Guardian or it refuses, by name, with this exception. Every
 * refusal is recorded (see {@see Refusals}) so a scenario run can report which
 * subsystems it still needs; the roadmap milestone that ports the subsystem is
 * part of the message.
 */
final class NotOnGuardianYet extends RuntimeException
{
    private function __construct(
        public readonly string $subsystem,
        public readonly string $milestone,
        string $message,
    ) {
        parent::__construct($message);
        Refusals::record($this);
    }

    /**
     * @param string $subsystem what asked (a class, `Class::method`, or a service id)
     * @param string $milestone the guardian-runner roadmap milestone that ports it (`M2`, …)
     */
    public static function for(string $subsystem, string $milestone, ?string $detail = null): self
    {
        $message = sprintf('%s is not on Guardian yet (guardian-kernel, roadmap %s)', $subsystem, $milestone);
        if ($detail !== null && $detail !== '') {
            $message .= ': ' . $detail;
        }

        return new self($subsystem, $milestone, $message);
    }

    /**
     * The refusal for a piece of code that still reaches for the SQL database:
     * `Pimcore\Db::get()`, `$dao->db`, `Db\Helper::…`. Names the caller.
     */
    public static function sql(string $what, string $milestone = 'M2'): self
    {
        $caller = self::caller();

        return self::for($caller ?? $what, $milestone, $what . ' — this kernel has no SQL database');
    }

    /**
     * The first frame outside this namespace and outside `Pimcore\Db`, as
     * `Class::method`.
     */
    private static function caller(): ?string
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12) as $frame) {
            $class = $frame['class'] ?? null;
            if ($class === null) {
                continue;
            }
            if (str_starts_with($class, __NAMESPACE__ . '\\') || $class === 'Pimcore\\Db' || str_starts_with($class, 'Pimcore\\Db\\')) {
                continue;
            }
            if ($class === 'Pimcore\\Model\\Dao\\AbstractDao') {
                continue;
            }

            return $class . '::' . ($frame['function'] ?? '?');
        }

        return null;
    }
}
