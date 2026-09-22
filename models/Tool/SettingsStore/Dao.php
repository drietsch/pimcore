<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */


namespace Pimcore\Model\Tool\SettingsStore;

use Pimcore\Guardian\CapabilityRefused;
use Pimcore\Model;
use Pimcore\Model\Tool\SettingsStore;

/**
 * The settings store on Guardian (guardian-kernel, guardian-runner ADR-0013):
 * one governed configuration entry of kind `pimcore-setting` per setting,
 * keyed `<scope>/<id>`. `config.define` writes, `config.read` reads,
 * `config.list` lists a scope, `config.delete` removes — the same commits,
 * policy and evidence every other write gets, which is why system settings
 * and bundle installation flags are auditable now and were not before.
 *
 * @internal
 *
 * @property SettingsStore $model
 */
class Dao extends Model\Dao\AbstractDao
{
    public const KIND = 'pimcore-setting';

    public function set(string $id, float|bool|int|string $data, string $type = SettingsStore::TYPE_STRING, ?string $scope = null): bool
    {
        try {
            $this->guardian->call('config.define', [
                'kind' => self::KIND,
                'definition' => json_encode([
                    'scope' => (string) $scope,
                    'id' => $id,
                    'type' => $type,
                    'data' => self::toText($data, $type),
                ], JSON_THROW_ON_ERROR),
                'message' => sprintf('set setting %s/%s', (string) $scope, $id),
            ]);

            return true;
        } catch (CapabilityRefused) {
            return false;
        }
    }

    public function delete(string $id, ?string $scope = null): int|string
    {
        try {
            $this->guardian->call('config.delete', [
                'kind' => self::KIND,
                'id' => self::key($scope, $id),
                'message' => sprintf('delete setting %s/%s', (string) $scope, $id),
            ]);

            return 1;
        } catch (CapabilityRefused) {
            return 0;
        }
    }

    /**
     * @throws Model\Exception\NotFoundException
     */
    public function getById(string $id, ?string $scope = null): void
    {
        try {
            $out = $this->guardian->call('config.read', [
                'kind' => self::KIND,
                'id' => self::key($scope, $id),
            ]);
        } catch (CapabilityRefused) {
            throw new Model\Exception\NotFoundException('settings store with id ' . $id . ' and scope ' . $scope . ' not found');
        }

        $view = json_decode((string) ($out['view'] ?? ''), true);
        if (!is_array($view)) {
            throw new Model\Exception\NotFoundException('settings store with id ' . $id . ' and scope ' . $scope . ' not found');
        }

        $this->assignVariablesToModel([
            'id' => $view['id'] ?? $id,
            'scope' => $view['scope'] ?? (string) $scope,
            'type' => $view['type'] ?? SettingsStore::TYPE_STRING,
        ]);
        $this->model->setData($view['data'] ?? null);
    }

    /**
     * @return string[]
     */
    public function getIdsByScope(string $scope): array
    {
        $out = $this->guardian->call('config.list', ['kind' => self::KIND]);

        $ids = [];
        $prefix = $scope . '/';
        foreach ((array) ($out['names'] ?? []) as $name) {
            $name = (string) $name;
            if (str_starts_with($name, $prefix)) {
                $ids[] = substr($name, strlen($prefix));
            }
        }

        return $ids;
    }

    /**
     * The key of a setting, as `runner-config` derives it: scope first, so a
     * scope listing is a prefix.
     */
    private static function key(?string $scope, string $id): string
    {
        return (string) $scope . '/' . $id;
    }

    private static function toText(float|bool|int|string $data, string $type): string
    {
        if ($type === SettingsStore::TYPE_BOOLEAN) {
            return $data ? '1' : '0';
        }

        return (string) $data;
    }
}
