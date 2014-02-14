<?php
namespace Phalcon\Logger\Adapter\File;

use Phalcon\Logger\Exception as LoggerException;
use Phalcon\Logger as Logger;

/**
 * Phalcon\Logger\Adapter\File\Multiple
 *
 * Adapter to save logs into multiple log files based on their level.
 *
 * TODO Implement transactions.
 *
 * @version 0.1
 * @author Richard Laffers <richard.laffers@movys.sk>
 * @license The BSD 3-Clause License {@link http://opensource.org/licenses/BSD-3-Clause}
 */
class Multiple extends \Phalcon\Logger\Adapter\File implements \Phalcon\Logger\AdapterInterface
{

    /**
     * Path to the directory where log files will be saved. No trailing slash.
     *
     * @var string
     */
    protected $path;

    /**
     * Adapter options.
     *
     * @var array
     */
    protected $options;

    /**
     * Class constructor.
     *
     * @param string $path    Directory path for saving the log files.
     * @param array  $options The following options are available:
     *              `           extension   (string) Extension for all log files.
     *              `           prefix      (string) Name prefix for all log files
     * @throws \Phalcon\Logger\Exception
     */
    public function __construct($path, $options = array())
    {
        $path = rtrim($path, ' ' . \DIRECTORY_SEPARATOR);
        if (!file_exists($path) || !is_dir($path)) {
            throw new LoggerException('Directory ' . $path . ' does not exist!');
        }

        if (!is_writable($path)) {
            throw new LoggerException('Directory ' . $path . ' is not writable!');
        }

        $this->path = $path;

        $defaults = array(
            'extension' => 'log',
            'prefix' => '',
        );

        $this->options = array_merge($defaults, $options);
    }

    /**
     * Writes the log to the file itself
     *
     * @param  string                    $message
     * @param  integer                   $type
     * @param  integer                   $time
     * @param  array                     $context
     * @throws \Phalcon\Logger\Exception
     */
    public function logInternal($message, $type, $time, $context = array())
    {
        $filename = $this->path .
            \DIRECTORY_SEPARATOR .
            $this->options['prefix'] .
            $this->getTypeString($type) .
            '.' .
            $this->options['extension'];

        $log    = $this->getFormatter()->format($message, $type, $time);
        $result = file_put_contents($filename, $log, \FILE_APPEND);

        if ($result === false) {
            throw new LoggerException('Failed to write log into ' . $filename);
        }

        return;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Phalcon\Logger\Exception
     */
    public function begin()
    {
        throw new LoggerException('Multiple file logger transactions are not implemented yet!');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Phalcon\Logger\Exception
     */
    public function commit()
    {
        throw new LoggerException('Multiple file logger transactions are not implemented yet!');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Phalcon\Logger\Exception
     */
    public function rollback()
    {
        throw new LoggerException('Multiple file logger transactions are not implemented yet!');
    }

    /**
     * getTypeString
     *
     * Translates Phalcon log types into type strings.
     *
     * TODO: It would be nice to make a config option to say which error levels go into what files.
     *
     * @param  integer $type
     * @return string
     */
    protected function getTypeString($type)
    {
        switch ($type) {
            case Logger::EMERGENCE:
            case Logger::CRITICAL:
                // emergence, critical
                return 'critical';
            case Logger::ALERT:
            case Logger::ERROR:
                // error, alert
                return 'error';
            case Logger::WARNING:
                // warning
                return 'warning';
            case Logger::NOTICE:
            case Logger::INFO:
                // info, notice
                return 'info';
            case Logger::DEBUG:
            case Logger::CUSTOM:
            case Logger::SPECIAL:
            default:
                // debug, log, custom, special
                return 'debug';
        }
    }
}
