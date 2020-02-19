<?php

namespace Reportico\Engine;

/**
 * Class to store global var
 * 
 */
class ReporticoSession
{

    private function __construct()
    {}

    private function __clone()
    {}

    // Return the namespace selected by an external GET or PORT in form
    // sessionid_namespacej
    static function switchToRequestedNamespace($default_namespace)
    {
        $session_name = $default_namespace;

        // Check for Posted Session Name and use that if specified
        if (isset($_REQUEST['reportico_session_name'])) {
            $session_name = $_REQUEST['reportico_session_name'];
            if (preg_match("/_/", $session_name)) {

                $ar = explode("_", $session_name);
                ReporticoApp::set("session_namespace", $ar[1]);
                if (ReporticoApp::get("session_namespace")) {
                    ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
                }

                // Set session to join only if it is not NS meaning its called from framework and existing session
                // should be used
                //if ($ar[0] != "NS") {
                    $session_name = $ar[1];
                //}

            }
        }
        return $session_name;
    }

    // Ensure that sessions from different browser windows on same devide
    // target separate SESSION_ID
    static function setUpReporticoSession($namespace)
    {
        // Get current session
        $session_name = session_id();

        // Check for Posted Session Name and use that if specified
        if (isset($_REQUEST['reportico_session_name'])) {
            $session_name = $_REQUEST['reportico_session_name'];
            if (preg_match("/_/", $session_name)) {
                $ar = explode("_", $session_name);
                ReporticoApp::set("session_namespace", $ar[1]);
                if (ReporticoApp::get("session_namespace")) {
                    ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
                }

                // Set session to join only if it is not NS meaning its called from framework and existing session
                // should be used
                if ($ar[0] != "NS") {
                    $session_name = $ar[0];
                }

            }
        } 

        // If the session_name starts with NS_ then it is a namespace for reportico
        // embedded in a framework or for multiple concurrent reportico instances
        // , so set the current namespace. All sessiion variables for this namespace
        // will be stored in a namspaces specific session array
        if (strlen($session_name) >= 3 && substr($session_name, 0, 3) == "NS_") {
            if (!$session_name || !isset($_SESSION)) {
                session_start();
            }

            ReporticoApp::set("session_namespace", substr($session_name, 3));

            // IF NS_NEW passed then autogenerate session namespace from current time
            if (ReporticoApp::get("session_namespace") == "NEW") {
                ReporticoApp::set("session_namespace", date("YmdHis"));
            }
            if (ReporticoApp::get("session_namespace")) {
                ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
            }

            if (isset($_REQUEST['clear_session']) && isset($_SESSION)) {
                self::initializeReporticoNamespace(self::reporticoNamespace());
            }
            return;
        }

        // If no current or specified session start one, or if request to clear session (it really means clear namespace)
        // then clear this out
        if (!$session_name || isset($_REQUEST['clear_session'])) {

            // If no session current then create a new one
            if (!$session_name || !isset($_SESSION)) {
                session_start();
            }

            if (isset($_REQUEST['new_session']) && $_REQUEST['new_session']) {
                session_regenerate_id(false);
            }

            //unsetReporticoSessionParam("template");
            //session_regenerate_id(false);
            $session_name = session_id();

            
            // If no session set ( a new session ) set the namespace to be called default
            if ( !$namespace ) {
                $namespace = "default";
            }

            if (isset($_REQUEST['clear_session'])) {
                ReporticoApp::set("session_namespace", $namespace);
                ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
                self::initializeReporticoNamespace(self::reporticoNamespace());
            }
        } else {
            if (session_id() != $session_name) {
                session_id($session_name);
                session_start();
            }

            if ( !$namespace ) {
                $namespace = "default";
            }
                //ReporticoApp::set("session_namespace", $namespace);
                ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
            $namespace = ReporticoApp::get("session_namespace");
            $session_name = session_id();
        }

    }

    /**
     * Does global reportico session exist
     * 
     * @return bool
     */
    static function existsReporticoSession()
    {
        if (isset($_SESSION[ReporticoApp::get("session_namespace_key")])) {
            return true;
        } else {
            return false;
        }

    }
    
    /*
     * Cleanly shuts down session
     */
    static function closeReporticoSession()
    {
        // PPP echo "ISSETEND? ".self::issetReporticoSessionParam("reportConfig")."<BR>";
        session_write_close();
    }

    static function sessionItem($in_item, $in_default = false)
    {
        $ret = false;
        if (self::issetReporticoSessionParam($in_item)) {
            $ret = self::getReporticoSessionParam($in_item);
        }

        if (!$ret) {
            $ret = false;
        }

        if ($in_default && !$ret) {
            $ret = $in_default;
        }

        self::setReporticoSessionParam($in_item, $ret);

        return ($ret);
    }

    static function sessionRequestItem($in_item, $in_default = false, $in_default_condition = true)
    {
        $ret = false;
        if (self::issetReporticoSessionParam($in_item)) {
            $ret = self::getReporticoSessionParam($in_item);
        }

        if (array_key_exists($in_item, $_REQUEST)) {
            $ret = $_REQUEST[$in_item];
        }

        if (!$ret) {
            $ret = false;
        }

        if ($in_default && $in_default_condition && !$ret) {
            $ret = $in_default;
        }

        self::setReporticoSessionParam($in_item, $ret);

        return ($ret);
    }

    /**
     * Check if a particular reeportico session parameter is set
     * using current session namespace
     * 
     * @param string $param Session parameter name
     * @param string $session_name Session name
     * 
     * @return bool 
     */
    static function issetReporticoSessionParam($param, $session_name = false)
    {
        if (!$session_name)
            $session_name = ReporticoApp::get("session_namespace_key");
        
        return isset($_SESSION[$session_name][$param]);
    }

    /**
     * Sets a reportico session_param using current session namespace
     * 
     * @param string $param Session parameter name
     * @param mixed $value Session parameter value
     * @param string $namespace Namespace session
     * @param array|bool ???? 
     * 
     * @return void
     */
    static function setReporticoSessionParam($param, $value, $namespace = false, $array = false)
    {
        if (!$namespace)
            $namespace = ReporticoApp::get("session_namespace_key");

        //echo "Set $namespace:$param<BR>";
        if (!$array) {
            $_SESSION[$namespace][$param] = $value;
        } else {
            $_SESSION[$namespace][$array][$param] = $value;
        }
    
    }

    /**
     * Return the value of a reportico session_param
     * using current session namespace
     * 
     * @param string $param Session parameter name
     * 
     * @return mixed
     */
    static function getReporticoSessionParam($param)
    {
        if(self::issetReporticoSessionParam($param))
            return $_SESSION[ReporticoApp::get("session_namespace_key")][$param];
        else
            return false;
    }

    /**
     * Clears a reportico session_param using current session namespace
     * 
     * @param string $param Session parameter name
     * @return void
     */
    static function unsetReporticoSessionParam($param)
    {
        if (isset($_SESSION[ReporticoApp::get("session_namespace_key")][$param])) {
            unset($_SESSION[ReporticoApp::get("session_namespace_key")][$param]);
        }
    }

    /*
     **
     ** Register a session variable which will remain persistent throughout session
     */
    static function registerSessionParam($param, $value)
    {
        if (!self::issetReporticoSessionParam($param)) {
            self::setReporticoSessionParam($param, $value);
        }

        return self::getReporticoSessionParam($param);
    }

    /*
     ** Returns the current session name.
     ** Session variables exist
     ** using current session namespace
     */
    static function reporticoSessionName()
    {
        //if ( ReporticoApp::get("session_namespace") )
        if (self::getReporticoSessionParam("framework_parent")) {
            return "NS_" . ReporticoApp::get("session_namespace");
        } else {
            return session_id() . "_" . ReporticoApp::get("session_namespace");
        }

    }

    /*
     ** Returns the current namespace
     */
    static function reporticoNamespace()
    {
        return ReporticoApp::get("session_namespace_key");
    }

    /*
     ** initializes a reportico namespace
     **
     */
    static function initializeReporticoNamespace($namespace)
    {
        $namespace = ReporticoApp::get("session_namespace_key");
        if (isset($_SESSION[$namespace])) {
            unset($_SESSION[$namespace]);
        }

        ReporticoSession::setReporticoSessionParam("awaiting_initial_defaults", true);
        ReporticoSession::setReporticoSessionParam("firsttimeIn", true);
    }
}
