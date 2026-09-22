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
use Throwable;

/**
 * Guardian did not produce an output for a call: denied by policy, refused
 * by the capability's contract, or failed. The message is Guardian's own,
 * never enriched here (guardian-runner ADR-0008 decision 5).
 */
final class CapabilityRefused extends RuntimeException
{
    public function __construct(public readonly string $capability, string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function from(string $capability, Throwable $cause): self
    {
        return new self($capability, $cause->getMessage(), $cause);
    }
}
