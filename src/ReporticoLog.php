<?php

namespace Reportico;

use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ReporticoLog
{

    /**
     * @var Singleton
     * @access private
     * @static
     */
    private static $_instance = null;

    public $debug_mode = false;

    //@var Monolog\Logger $logger logger used to manage log message
    private $logger;

    public function __construct()
    {
        //** Log management ***
        $this->logger = new Logger('reportico');
        //We store in reportico.log file all NOTICE message and above (WARNING, ERROR, etc.)
        $this->logger->pushHandler(new StreamHandler('log/reportico.log', Logger::WARNING));

        error_reporting(0);
    }

    public function getDebugMode()
    {
        return $this->debug_mode;
    }

    public function setDebugMode($value)
    {
        $this->debug_mode = $value;

        if ($this->getDebugMode()) {
            $browserHanlder = new \Monolog\Handler\BrowserConsoleHandler(\Monolog\Logger::DEBUG);
            $this->logger->pushHandler($browserHanlder);
        }

        error_reporting(E_ALL);
    }

    public function info($message)
    {
        $this->logger->info($message);
    }

    public function debug($message)
    {
        $this->logger->debug($message);
    }

    /**
     * Singleton instance
     *
     * @param void
     * @return Singleton
     */
    public static function getI()
    {

        if (is_null(self::$_instance)) {
            self::$_instance = new ReporticoLog();
        }

        return self::$_instance;
    }
}
