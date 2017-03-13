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
use Composer\Plugin\PluginInterface;

/**
 * Class ModuleInstallerPlugin, registers our installer with Composer
 *
 * @package oxidModuleConnector\Composer
 */
class ModuleInstallerPlugin implements PluginInterface
{

    /**
     * Logger utility
     *
     * @var Logger $logger
     */
    protected $logger;

    /**
     * Activate our plugin
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->logger = new Logger('omc-composer-installer', $io);
        $this->logger->debug("omc-composer-installer activated!");
        $installer = new ModuleInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}