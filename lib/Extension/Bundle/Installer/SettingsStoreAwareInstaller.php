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

namespace Pimcore\Extension\Bundle\Installer;

use Pimcore\Model\Tool\SettingsStore;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * A bundle's installation state lives in the settings store (on Guardian's
 * `config.*` in this kernel). Schema migrations are gone with the database
 * (guardian-kernel): a bundle that has state to migrate does it against
 * Guardian in `install()`/`uninstall()`.
 */
abstract class SettingsStoreAwareInstaller extends AbstractInstaller
{
    protected BundleInterface $bundle;

    public function __construct(BundleInterface $bundle)
    {
        parent::__construct();
        $this->bundle = $bundle;
    }

    protected function getSettingsStoreInstallationId(): string
    {
        return 'BUNDLE_INSTALLED__' . $this->bundle->getNamespace() . '\\' . $this->bundle->getName();
    }

    /**
     * @deprecated there are no migrations in this kernel; kept for bundles that override it.
     */
    public function getLastMigrationVersionClassName(): ?string
    {
        return null;
    }

    protected function markInstalled(): void
    {
        SettingsStore::set($this->getSettingsStoreInstallationId(), true, SettingsStore::TYPE_BOOLEAN, 'pimcore');
    }

    protected function markUninstalled(): void
    {
        SettingsStore::set($this->getSettingsStoreInstallationId(), false, SettingsStore::TYPE_BOOLEAN, 'pimcore');
    }

    public function install(): void
    {
        parent::install();
        $this->markInstalled();
    }

    public function uninstall(): void
    {
        parent::uninstall();
        $this->markUninstalled();
    }

    public function isInstalled(): bool
    {
        $installSetting = SettingsStore::get($this->getSettingsStoreInstallationId(), 'pimcore');

        return (bool) ($installSetting ? $installSetting->getData() : false);
    }

    public function canBeInstalled(): bool
    {
        return !$this->isInstalled();
    }

    public function canBeUninstalled(): bool
    {
        return $this->isInstalled();
    }
}
