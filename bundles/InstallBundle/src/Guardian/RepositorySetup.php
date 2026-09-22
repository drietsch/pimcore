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

namespace Pimcore\Bundle\InstallBundle\Guardian;

use Pimcore\Guardian\CapabilityRefused;
use Pimcore\Guardian\Client;
use RuntimeException;

/**
 * Prepares a Guardian repository for this installation (guardian-kernel,
 * guardian-runner ADR-0013). What `install.sql` did before: there is no
 * schema to create, so what is left is the facts an installation needs —
 * the three tree roots and the admin identity — and each one is a governed
 * commit.
 *
 * The roots carry Studio's tree-root designation (`guardian:studio:tree-root:
 * object|asset|document`), so Studio and a later `guardian-bridge import`
 * of the same instance agree on which folder is which tree's origin.
 *
 * @internal
 */
final class RepositorySetup
{
    /** The three trees Pimcore keeps and Studio shows. */
    public const TREES = ['object' => 'objects', 'asset' => 'assets', 'document' => 'documents'];

    public function __construct(private readonly Client $guardian = new Client())
    {
    }

    /**
     * Whether this process can reach Guardian at all.
     */
    public function isReachable(): bool
    {
        return Client::available();
    }

    /**
     * The repository this installation writes to, as `session.whoami` reports it.
     *
     * @return array{principal: string, branch: string}
     */
    public function describe(): array
    {
        $out = $this->guardian->call('session.whoami');

        return ['principal' => (string) ($out['principal'] ?? ''), 'branch' => (string) ($out['branch'] ?? '')];
    }

    /**
     * Create the three tree roots, unless they are already there. Returns the
     * trees this call created, in order.
     *
     * @return list<string>
     */
    public function createTreeRoots(): array
    {
        $created = [];
        foreach (self::TREES as $tree => $key) {
            if ($this->resolveRoot($tree) !== null) {
                continue;
            }
            $element = $this->guardian->call('element.create', [
                'kind' => 'folder',
                'key' => $key,
                'parent' => '',
            ]);
            $id = (string) ($element['element'] ?? '');
            if ($id === '') {
                throw new RuntimeException(sprintf('Guardian did not return an element id for the %s tree root', $tree));
            }
            $this->guardian->call('element.alias', [
                'element' => $id,
                'alias' => sprintf('guardian:studio:tree-root:%s', $tree),
                'op' => 'add',
            ]);
            $created[] = $tree;
        }

        return $created;
    }

    /**
     * The element id of a tree's root, or null when the tree has none yet.
     *
     * Read from the alias registry (`element.aliases`), which is where the
     * designation lives — the same index the Bridge and Studio read.
     */
    public function resolveRoot(string $tree): ?string
    {
        $wanted = sprintf('guardian:studio:tree-root:%s', $tree);
        try {
            $out = $this->guardian->call('element.aliases', ['source' => 'guardian']);
        } catch (CapabilityRefused) {
            return null;
        }

        $aliases = (array) ($out['aliases'] ?? []);
        $elements = (array) ($out['elements'] ?? []);
        foreach ($aliases as $i => $alias) {
            if ((string) $alias === $wanted) {
                $id = (string) ($elements[$i] ?? '');

                return $id === '' ? null : $id;
            }
        }

        return null;
    }

    /**
     * @param array{username: string, password: string} $credentials
     *
     * @return list<string>
     */
    public function validateAdminCredentials(array $credentials): array
    {
        $errors = [];

        if (strlen($credentials['username']) < 4) {
            $errors[] = 'Admin username must be at least 4 characters';
        }

        if (strlen($credentials['password']) < 4) {
            $errors[] = 'Admin password must be at least 4 characters';
        }

        return $errors;
    }

    /**
     * Create or replace the administrator.
     *
     * Identities live in Guardian's directory (guardian-runner roadmap M1);
     * until the directory capabilities carry Pimcore's user model, the
     * installation records the administrator as a setting so the kernel can
     * boot and the refusal names what is missing.
     *
     * @param array{username: string, password: string} $credentials
     */
    public function createOrUpdateAdminUser(array $credentials): void
    {
        \Pimcore\Model\Tool\SettingsStore::set(
            'ADMIN_USER',
            $credentials['username'],
            \Pimcore\Model\Tool\SettingsStore::TYPE_STRING,
            'pimcore',
        );
        \Pimcore\Model\Tool\SettingsStore::set(
            'ADMIN_PASSWORD_HASH',
            password_hash($credentials['username'] . ':pimcore:' . $credentials['password'], PASSWORD_DEFAULT),
            \Pimcore\Model\Tool\SettingsStore::TYPE_STRING,
            'pimcore',
        );
    }
}
