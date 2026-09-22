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

namespace Pimcore\Bundle\InstallBundle\EnvVarDefinition\Definitions;

use Pimcore\Bundle\InstallBundle\EnvVarDefinition\MessengerTransportDefinitionInterface;

/**
 * The messenger transport of this kernel: Guardian jobs (guardian-kernel,
 * guardian-runner roadmap M5). No user input — the node the plane opens is
 * the transport, so there is nothing to configure here.
 *
 * The trailing ?queue_name= is required because Pimcore appends queue names
 * directly to this value in bundle YAML configs.
 */
final readonly class GuardianMessengerEnvVarDefinition implements
    MessengerTransportDefinitionInterface
{
    public function getKey(): string
    {
        return 'messenger-guardian';
    }

    public function getLabel(): string
    {
        return 'Messenger Transport (Guardian jobs)';
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function getSectionName(): string
    {
        return 'pimcore/pimcore';
    }

    public function getParameters(): array
    {
        return [];
    }

    public function resolveEnvVars(array $collectedValues): array
    {
        return [
            'PIMCORE_MESSENGER_TRANSPORT_DSN_PREFIX' => 'guardian://jobs?queue_name=',
        ];
    }

    public function validate(array $collectedValues): array
    {
        return [];
    }
}
