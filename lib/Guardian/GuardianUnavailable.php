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
 * This kernel is running outside guardian-runner: `runtime_call` does not
 * exist, so nothing that needs Guardian can work.
 */
final class GuardianUnavailable extends RuntimeException
{
    public static function noDoor(): self
    {
        return new self('this Pimcore kernel (guardian-kernel) runs on guardian-runner: runtime_call() is not defined in this process');
    }
}
