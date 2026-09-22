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

use Error;

/**
 * The kernel's door to Guardian: one call, `runtime_call`, which guardian-runner
 * registers in every isolate and which dispatches through Guardian's
 * capability registry under the plane's principal (guardian-runner ADR-0008).
 *
 * This is the hand-written core; the typed per-capability methods are generated
 * from the capability catalog (`Pimcore\Guardian\Generated\…`, guardian-runner
 * roadmap M1) and call {@see call()}.
 */
final class Client
{
    private static ?self $instance = null;

    /** @var array<string, true>|null */
    private ?array $catalog = null;

    public static function get(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Whether this process runs inside guardian-runner (the door exists).
     */
    public static function available(): bool
    {
        return function_exists('runtime_call');
    }

    /**
     * Invoke a capability. `$input` is the capability's input as PHP arrays
     * and scalars; the output comes back the same way.
     *
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     *
     * @throws GuardianUnavailable when not running inside guardian-runner
     * @throws CapabilityRefused when Guardian refused or the call failed
     */
    public function call(string $capability, array $input = []): array
    {
        if (!self::available()) {
            throw GuardianUnavailable::noDoor();
        }

        try {
            $out = runtime_call($capability, $input);
        } catch (Error $e) {
            throw CapabilityRefused::from($capability, $e);
        }

        return is_array($out) ? $out : [];
    }

    /**
     * Whether the registry serves `$capability` for this principal.
     */
    public function has(string $capability): bool
    {
        if ($this->catalog === null) {
            $this->catalog = [];
            if (self::available() && function_exists('runtime_capabilities')) {
                foreach ((array) runtime_capabilities() as $name) {
                    $this->catalog[(string) $name] = true;
                }
            }
        }

        return isset($this->catalog[$capability]);
    }
}
