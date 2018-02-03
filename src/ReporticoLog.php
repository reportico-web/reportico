<?php

namespace Reportico\Engine;

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
        $this->logger->pushHandler(new StreamHandler('log/reportico.log', Logger::INFO));

        error_reporting(0);
    }

    public function getDebugMode()
    {
        return $this->debug_mode;
    }

    private function setDebugMode($value)
    {
        $this->debug_mode = $value;

        if ($this->debug_mode) {
            $browserHandler = new \Monolog\Handler\BrowserConsoleHandler(\Monolog\Logger::DEBUG);
            $this->logger->pushHandler($browserHandler);
        }

        error_reporting(E_ALL);
    }
    
    /*
    * Shortcut function to activate debug mode
    */
    public static function activeDebugMode($TrueOrFalse = true){
        $log = new ReporticoLog();
        $log->getI()->setDebugMode($TrueOrFalse);
        if($TrueOrFalse)
            ReporticoLog::debug("*** Debug activation ****");
        else
            ReporticoLog::debug("*** Debug desactivation ****");
    }
    
    

    /*
    * Function to store information Log
    */
    public static function info($message)
    {
        $log = new ReporticoLog();
        $log->getI()->logger->info($message);
    }

    public static function debug($message)
    {
         $log = new ReporticoLog();
        $log->getI()->logger->debug($message);
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
