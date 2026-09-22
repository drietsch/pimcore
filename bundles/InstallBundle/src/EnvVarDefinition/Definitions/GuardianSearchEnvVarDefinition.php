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

use Pimcore\Bundle\InstallBundle\EnvVarDefinition\SearchEngineDefinitionInterface;

/**
 * The search engine of this kernel: Guardian's own index (Tantivy), reached
 * through `element.search` / `element.query` (guardian-kernel, guardian-runner
 * roadmap M3). There is no OpenSearch and no Elasticsearch, and nothing to
 * configure: the node the plane opens is the index.
 */
final readonly class GuardianSearchEnvVarDefinition implements SearchEngineDefinitionInterface
{
    public function getKey(): string
    {
        return 'guardian-search';
    }

    public function getLabel(): string
    {
        return 'Search (Guardian index)';
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
        return [];
    }

    public function validate(array $collectedValues): array
    {
        return [];
    }
}
