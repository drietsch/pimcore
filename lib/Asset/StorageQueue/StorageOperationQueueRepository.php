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

namespace Pimcore\Asset\StorageQueue;

use Pimcore\Guardian\NotOnGuardianYet;

/**
 * The asset storage operation queue deferred path moves on a path-addressed
 * storage. On Guardian assets are content-addressed (guardian-kernel,
 * guardian-runner roadmap M4): there is nothing to move, so the queue is
 * always empty and every write to it refuses by name.
 *
 * @internal
 */
final class StorageOperationQueueRepository implements StorageOperationQueueRepositoryInterface
{
    public function add(StorageOperation $operation): void
    {
        throw $this->retired(__FUNCTION__);
    }

    public function repointMoves(string $storage, string $movedPrefix, string $newPrefix): void
    {
        throw $this->retired(__FUNCTION__);
    }

    public function findCovering(string $storage, string $logicalPath): array
    {
        return [];
    }

    public function findWithTargetUnder(string $storage, string $prefix): array
    {
        return [];
    }

    public function findSourceCovering(string $storage, string $path): array
    {
        return [];
    }

    public function hasOperations(string $storage): bool
    {
        return false;
    }

    public function all(): array
    {
        return [];
    }

    public function findById(int $id): ?StorageOperation
    {
        return null;
    }

    public function remove(int $id): void
    {
        throw $this->retired(__FUNCTION__);
    }

    public function removeIfUnchanged(StorageOperation $operation): bool
    {
        throw $this->retired(__FUNCTION__);
    }

    private function retired(string $method): NotOnGuardianYet
    {
        return NotOnGuardianYet::for(self::class . '::' . $method, 'M4', 'asset storage is content-addressed on Guardian; the path move queue is retired');
    }
}
