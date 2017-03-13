<?php
/**
 * Composer installer for ioly / Oxid Module Connector
 *
 * @license   MIT License http://opensource.org/licenses/MIT
 * @author    Stefan Moises, <stefan@rent-a-hero.de>
 * @version   1.0.0
 * @link      https://github.com/OXIDprojects/OXID-Module-Connector
 */
namespace oxidModuleConnector\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Installer\InstallerInterface;
use Composer\Repository\InstalledRepositoryInterface;
use ioly\ioly;

/**
 * Class ModuleInstaller - the main installer logic
 *
 * @package oxidModuleConnector\Composer
 */
class ModuleInstaller extends LibraryInstaller
{

    /**
     * @var Composer $composer
     */
    protected $composer;
    /**
     * Ioly Core
     *
     * @var Ioly
     */
    protected $ioly;
    /**
     * Logger utility
     *
     * @var Logger $logger
     */
    protected $logger;
    /**
     * Extra settings
     *
     * @var array
     */
    protected $pluginExtra = null;
    /**
     * Only run once during composer run
     *
     * @var bool
     */
    protected $iolyTriggered = false;
    /**
     * Always run OMC or only on main module install / update?
     *
     * @var bool
     */
    protected $alwaysRunOmc = true;

    /**
     * ModuleInstaller constructor.
     *
     * @param IOInterface          $io
     * @param Composer             $composer
     * @param string               $type
     * @param Filesystem|null      $filesystem
     * @param BinaryInstaller|null $binaryInstaller
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'library', Filesystem $filesystem = null, BinaryInstaller $binaryInstaller = null)
    {
        parent::__construct($io, $composer, $type, $filesystem, $binaryInstaller);

        $this->composer = $composer;
        $this->logger = new Logger('omc-composer-installer', $io);
        $extra = $this->getPluginExtra();
        if (isset($extra['settings']) && isset($extra['settings']['alwaysRunOnUpdate']) && $extra['settings']['alwaysRunOnUpdate'] == "false") {
            $this->alwaysRunOmc = false;
        }
        $this->initIoly();
    }

    /**
     * Load data from "extra" section
     *
     * @return array
     */
    public function getPluginExtra()
    {
        if ($this->pluginExtra === null) {
            $this->pluginExtra = array();
            if ($this->composer->getPackage()) {
                $extra = $this->composer->getPackage()->getExtra();
                if (!empty($extra['omc-composer-installer']) && is_array($extra['omc-composer-installer'])) {
                    $this->pluginExtra = $extra['omc-composer-installer'];
                    $this->logger->debug("omc-composer-installer settings found in extras: " . print_r($this->pluginExtra, true));
                }
            }
        }

        return $this->pluginExtra;
    }

    /**
     * Init ioly installer class
     *
     * @return null
     */
    public function initIoly()
    {
        $this->ioly = new ioly();
        $vendorPath = $this->composer->getConfig()->get('vendor-dir');
        $this->ioly->setSystemBasePath($vendorPath . DIRECTORY_SEPARATOR . "..");
        // add new OXID Connector cookbook instead of old ioly cookbook!
        $this->ioly->removeCookbook('ioly');
    }

    /**
     * {@inheritDoc}
     */
    public function getPackageBasePath(PackageInterface $package)
    {
        $basePath = parent::getPackageBasePath($package);
        $this->logger->debug("basePath: " . $basePath);

        return $basePath;
    }

    /**
     * Reads "extra" section of composer file and tries to install any
     * omc modules defined in there :)
     *
     * @return bool
     */
    public function handleOmcPackages()
    {
        if ($this->iolyTriggered) {
            $this->logger->warning("omc install already triggered!");

            return true;
        }
        if ($this->composer->getPackage()) {
            $extra = $this->getPluginExtra();
            $shopVersion = $extra['oxidversion'];
            if (empty($shopVersion)) {
                $this->logger->warning("Please set extra > omc-composer-installer > oxidversion in composer.json!");

                return false;
            }
            $this->ioly->setSystemVersion($shopVersion);
            $this->iolyTriggered = true;
            // set cookbooks
            $aCookbooks = $extra['cookbooks'];
            if (is_array($aCookbooks)) {
                foreach ($aCookbooks as $cookbookName => $cookbookUrl) {
                    $this->ioly->removeCookbook($cookbookName);
                    $this->logger->info("Setting cookbook: $cookbookName - $cookbookUrl");
                    $this->ioly->addCookbook($cookbookName, $cookbookUrl);
                }
            }
            // now install sub-module!
            $aModules = $extra['modules'];
            if (is_array($aModules)) {
                foreach ($aModules as $moduleName => $moduleVersion) {
                    if (!$this->ioly->isInstalledInVersion($moduleName, $moduleVersion)) {
                        $this->logger->info("Installing $moduleName in version $moduleVersion ...");
                        $this->ioly->install($moduleName, $moduleVersion);
                    } else {
                        $this->logger->info("$moduleName already installed in version $moduleVersion!");
                    }
                }
            }
        }

        return true;
    }

    /**
     * Installs specific package.
     *
     * @param InstalledRepositoryInterface $repo    repository in which to check
     * @param PackageInterface             $package package instance
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->logger->debug("install");
        parent::install($repo, $package);
        if (!$this->alwaysRunOmc) {
            $this->logger->info("running on install");
            $this->handleOmcPackages();
        }
    }

    /**
     * Updates specific package.
     *
     * @param InstalledRepositoryInterface $repo    repository in which to check
     * @param PackageInterface             $initial already installed package version
     * @param PackageInterface             $target  updated version
     *
     * @throws InvalidArgumentException if $initial package is not installed
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);

        if (!$this->alwaysRunOmc) {
            $this->logger->info("running on update");
            $this->handleOmcPackages();
        }
    }

    /**
     * Uninstalls specific package.
     *
     * @param InstalledRepositoryInterface $repo    repository in which to check
     * @param PackageInterface             $package package instance
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->logger->debug("uninstall");
        parent::uninstall($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        $sup = 'omc-library' === $packageType;
        // always run OMC if a supported package is found,
        // not only on un-/install or update!?
        if ($sup && $this->alwaysRunOmc) {
            $this->logger->debug("running on supports");
            $this->handleOmcPackages();
        }

        return $sup;
    }

    /**
     * Returns the installation path of a package
     *
     * @param  PackageInterface $package
     *
     * @return string           path
     */
    public function getInstallPath(PackageInterface $package)
    {
        $parentPath = parent::getInstallPath($package);
        $names = $package->getNames();
        $this->logger->debug("parentPath: $parentPath - packageNames: " . print_r($names, true));

        return $parentPath;
    }
}
