<?php

namespace UnvSoft\ComposerExtraInstaller;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Repository\InstalledFilesystemRepository;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;

/**
 * Class Plugin
 *
 * @TODO: add dependency package resolves. Now only specified package will be installed
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    protected $composer;
    protected $io;

    private $localRepo = null;
    private $repositoryManager = null;

    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            "post-install-cmd" => array(
                array("onPostUpdateInstall", 0)
            ),
            "post-update-cmd" => array(
                array("onPostUpdateInstall", 0)
            )
        );
    }

    /**
     * Plugin processing event post-install-cmd and post-update-cmd.
     *
     * @param Event $event
     *
     * @return void
     */
    public function onPostUpdateInstall(Event $event)
    {
        $extra = $event->getComposer()->getPackage()->getExtra();
        $packagesList = array();

        if (isset($extra["extra-dev"])) {
            $packagesList = array_merge($packagesList, $extra["extra-dev"]);
        }

        if (isset($extra["extra-dev-win"]) && $this->isWinOs()) {
            $packagesList = array_merge($packagesList, $extra["extra-dev-win"]);
        }
		
		if (isset($extra["extra-dev-unix"]) && !$this->isWinOs()) {
            $packagesList = array_merge($packagesList, $extra["extra-dev-unix"]);
        }

        $this->removeUnusedPackages($packagesList);
        if (!empty($packagesList)) {
            $this->installPackages($packagesList);
        }
    }

    /**
     * Install specified packages.
     *
     * @param mixed $packagesList array of packages, where key is package name, and value is version
     *
     * @return  void
     */
    protected function installPackages($packagesList)
    {
        $installationManager = $this->composer->getInstallationManager();
        $installationManager->disablePlugins();

        $localRepo = $this->getLocalRepository();
        foreach ($packagesList as $packageName => $packageVersion) {
            $package = $this->findPackage($packageName, $packageVersion);
            if (!$installationManager->isPackageInstalled($localRepo, $package)) {
                $installationManager->install(
                    $localRepo,
                    new InstallOperation($package)
                );
            }
        }

        $localRepo->write();
    }


    /**
     * Remove obsolete and not defined packages.
     *
     * @param $packagesList     array of packages, where key is package name, and value is version
     *
     */
    protected function removeUnusedPackages($packagesList)
    {
        $localRepo = $this->getLocalRepository();
        $installationManager = $this->composer->getInstallationManager();

        $installedPackages = $localRepo->getCanonicalPackages();
        foreach ($installedPackages as $package) {
            if (!isset($packagesList[$package->getName()])) {
                $installationManager->uninstall(
                    $localRepo,
                    new UninstallOperation($package)
                );
                continue;
            }

            $needPackage = $this->findPackage($package->getName(), $packagesList[$package->getName()]);
            if (($needPackage->__toString() != $package->__toString())
                || ((strstr($package->getVersion(), '999999') === 0)
                    && $needPackage->getSourceReference() != $package->getSourceReference())
            ) {
                $installationManager->uninstall(
                    $localRepo,
                    new UninstallOperation($package)
                );
            }
        }

        $localRepo->write();
    }

    /**
     * Return composer local repository for extra packages (vendor/composer/installed_extra.json).
     *
     * @return InstalledFilesystemRepository
     */
    protected function getLocalRepository()
    {
        if (!$this->localRepo){
            $this->localRepo = new InstalledFilesystemRepository(
                new JsonFile(
                    $this->composer->getConfig()->get("vendor-dir") . "/composer/installed_extra.json"
                )
            );
        }

        return $this->localRepo;
    }

    /**
     * Try to find composer package.
     *
     * @param $name          name of package
     * @param $version       version package
     *
     * @return Composer\Package\PackageInterface founded package
     *
     * @throws \Exception   If can't find package
     */
    protected function findPackage($name, $version)
    {
        if (!$this->repositoryManager) {
            $this->repositoryManager = $this->composer->getRepositoryManager();
        }

        $package = $this->repositoryManager->findPackage($name, $version);
        if (is_null($package)) {
            throw new \Exception(
                sprintf("Can't find package '%s' version '%s' ", $name, $version)
            );
        }

        return $package;
    }

    /**
     * Detect is Windows family operating system.
     *
     * @return bool TRUE if Windows family OS; FALSE otherwise
     */
    private function isWinOs()
    {
        return DIRECTORY_SEPARATOR === "\\";
    }
}
