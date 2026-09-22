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

/**
 * Every {@see NotOnGuardianYet} raised in this process, in order: the
 * executable seam count of guardian-runner ADR-0002 E3. A milestone's scenario
 * set is complete when this list is empty after the run.
 */
final class Refusals
{
    /** @var list<NotOnGuardianYet> */
    private static array $recorded = [];

    public static function record(NotOnGuardianYet $refusal): void
    {
        self::$recorded[] = $refusal;
    }

    /** @return list<NotOnGuardianYet> */
    public static function all(): array
    {
        return self::$recorded;
    }

    /**
     * Distinct subsystems refused, with their milestone and how often.
     *
     * @return array<string, array{milestone: string, count: int}>
     */
    public static function summary(): array
    {
        $out = [];
        foreach (self::$recorded as $r) {
            $out[$r->subsystem] ??= ['milestone' => $r->milestone, 'count' => 0];
            $out[$r->subsystem]['count']++;
        }
        ksort($out);

        return $out;
    }

    public static function reset(): void
    {
        self::$recorded = [];
    }
}
