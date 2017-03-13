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

use Composer\IO\IOInterface;

/**
 * Logging wrapper for Composer\IO\IOInterface
 *
 * @package oxidModuleConnector\Composer
 */
class Logger
{
    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var IOInterface $inputOutput
     */
    protected $inputOutput;

    /**
     * @param string $name
     * @param IOInterface $io
     */
    public function __construct($name, IOInterface $io)
    {
        $this->name = $name;
        $this->inputOutput = $io;
    }

    /**
     * Log a debug message
     *
     * Messages will be output at the "very verbose" logging level (eg `-vv`
     * needed on the Composer command).
     *
     * @param string $message
     */
    public function debug($message)
    {
        if ($this->inputOutput->isVeryVerbose()) {
            $message = "  <info>[{$this->name}]</info> {$message}";
            $this->log($message);
        }
    }

    /**
     * Log an informative message
     *
     * Messages will be output at the "verbose" logging level (eg `-v` needed
     * on the Composer command).
     *
     * @param string $message
     */
    public function info($message)
    {
        if ($this->inputOutput->isVerbose()) {
            $message = "  <info>[{$this->name}]</info> {$message}";
            $this->log($message);
        }
    }

    /**
     * Log a warning message
     *
     * @param string $message
     */
    public function warning($message)
    {
        $message = "  <error>[{$this->name}]</error> {$message}";
        $this->log($message);
    }

    /**
     * Write a message
     *
     * @param string $message
     */
    protected function log($message)
    {
        if (method_exists($this->inputOutput, 'writeError')) {
            $this->inputOutput->writeError($message);
        } else {
            // @codeCoverageIgnoreStart
            // Backwards compatiblity for Composer before cb336a5
            $this->inputOutput->write($message);
            // @codeCoverageIgnoreEnd
        }
    }
}
