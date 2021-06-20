<?php

namespace Reportico\Engine;

/**
 * Class Authenticator
 *
 * Base class for controlling access to Reportico functions
 */
class Authenticator extends ReporticoObject
{

    const UNKNOWN_ROLE = 0;
    const GUEST_ROLE = 1;
    const USER_ROLE = 2;
    const ADMIN_ROLE = 3;

    //public const GUEST_ROLE = 1;
    //public const ADMIN_ROLE = 2;
    //public const USER_ROLE = 3;

    //public const MODE_DESIGN = 1;
    //public const MODE_DEMO = 2;
    //public const MODE_RUN = 3;

    private static $_instance;

    public $roles = [
        "admin" => [
            "admin-login",
            "project-menu-page",
            "admin-page",
            "design-page",
            "design",
            "project",
            "report",
            "report-output",
            "criteria",
            "execute",
            "save",
            "access",
        ],
        "all-projects" => [
            "admin-page",
            "project",
            "report",
            "report-output",
            "criteria",
            "execute",
            "project-menu-page",
            "access",
        ],
        "design-fiddle" => [
            "design",
            "project",
            "project-menu-page",
            "criteria",
            "safe",
            "execute",
            "access",
        ],
        "project" => [
            "project-menu-page",
            "criteria",
            "execute",
            "report",
            "access",
        ],
        "report" => [
            "criteria",
            "execute",
            "access",
        ],
        "report-output" => [
            "execute",
            "access",
        ],
        "guest"
    ];

    public $usage = array(
        "description" => "Authenticate",
        "methods" => array( ),
        );

    public $query = false;
    public $type = false;

    public $role = false;
    public $widgets = false;
    public $engine = false;

    public $permissions = false;

    // Flattened permissions
    private $_permissions = [];
    private $revokes = [];
    private $flags = [];
    protected $_defaultPermissions = ["guest"];
    protected $_defaultRevokes = [];

    public function __construct($engine)
    {
        $this->role = self::UNKNOWN_ROLE;
        $this->engine = $engine;
        parent::__construct();
    }

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public static function __callStatic($method, $args)
    {
        //echo "<BR>CALL satic $method<BR>";
        switch ( $method ) {

            case "reset":
            case "grant":
            case "revoke":
            case "login":
            case "allowed":
            case "flag":
            case "unflag":
                $instance = self::getInstance();
                $_method = "_$method";
                return $instance->$_method(...$args);
                break;

            case "build":
                $builder = $args[0];
                if (isset($args[1]))  {
                    $object = new \Reportico\Engine\ReporticoCriteria();
                    $object->query = new Reportico();

                    $builder->engine->setProjectEnvironment($builder->engine->initial_project, $builder->engine->projects_folder, $builder->engine->admin_projects_folder);
                    $builder->engine->datasource = new ReporticoDataSource($builder->engine->external_connection, $builder->engine->available_connections);
                    $builder->engine->datasource->connect();
                    $object->criteriaItem = $builder->engine->setCriteriaLookup($args[1], $object->query);
                    $object->criteriaItem->datasource = $builder->engine->datasource;

                    $builder->stepInto("criteria", $object, "\Reportico\Engine\ReportCriteria");
                } else {
                    trigger_error("criteria method requires 1 parameter<BR>".$this->builderMethodUsage($level, "criteria"), E_USER_ERROR);
                }
                $object->builder = $builder;
                return $builder;
                break;

        }
    }

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public function __call($method, $args)
    {
        $exitLevel = false;

        if (!$this->builderMethodValid("criteria", $method, $args)) {
            return false;
        }

        // PPP echo "<BR>============ crit $method <BR>";
        switch ( strtolower($method) ) {

            case "end":
            default:
                $this->levelRef = false;
                $exitLevel = true;
                break;
        }

        if (!$exitLevel)
            return $this;

        return false;

    }

    public static function _flag ( $flags ) {

        $instance = self::getInstance();

        if ( is_string($flags) ) {
            if ( isset($instance->revokes[$flags] ))
                unset($instance->revokes[$flags]);
            $instance->flags[$flags] = $flags;
        }

        if ( is_array($flags) ) {
            foreach ($flags as $flag) {
                if ( isset($instance->revokes[$flag] ))
                    unset($instance->revokes[$flag]);
                $instance->flags[$flag] = $flag;
            }
        }
    }

    public static function _unflag ( $flags ) {

        $instance = self::getInstance();

        if ( is_string($flags) ) {
            if ( isset($instance->flags[$flags] ))
                unset($instance->flags[$flags]);
        }

        if ( is_array($flags) ) {
            foreach ($flags as $flag) {
                if ( isset($instance->flags[$flag] ))
                    unset($instance->flags[$flag]);
            }
        }
    }

    /*
     * Resets all permissions to the passed roles otherwise just clears the  permissions arrays
     */
    public static function _reset ( $permissions = false ) {

        $instance = self::getInstance();

        $instance->permissions = [];
        $instance->_permissions = [];
        $instance->revokes = [];
        $instance->flags = [];

        if ( $permissions )
            self::grant($permissions);
    }

    public static function _grant( $permissions ) {


        //echo "GRANT            ".$permissions."!!!<BR>";
        //echo "<PRE>";
        //debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        //echo "</PRE>";
        $instance = self::getInstance();

        $instance->_permissions = false;

        if ( is_string($permissions) ) {
            if ( isset($instance->revokes[$permissions] ))
               unset($instance->revokes[$permissions]);
            $instance->permissions[$permissions] = $permissions;
        }

        if ( is_array($permissions) ) {
            foreach ($permissions as $permission) {
                if ( isset($instance->revokes[$permission] ))
                    unset($instance->revokes[$permission]);
                $instance->permissions[$permission] = $permission;
            }
        }
        //self::show("granted");

    }

    public static function _revoke ( $permissions ) {

        //echo "======================== REVOKE $permissions <BR>";
        $instance = self::getInstance();

        $instance->_permissions = false;

        if ( is_string($permissions) ) {
            $instance->revokes[$permissions] = $permissions;
            unset($instance->permissions[$permissions]);
        }

        if ( is_array($permissions) ) {
            foreach ($permissions as $permission) {
                $instance->revokes[$permission] = $permission;
                unset($instance->permissions[$permission]);
            }
        }
        //echo "<BR><<<<<<<<<<<<<<<<<<<<<<<<<REVOKED $permissions <BR>";
    }

    public static function resumeFromSession ($allowed = [], $revoked = []) {

        $instance = self::getInstance();
        $sessionClass = \Reportico\Engine\ReporticoSession();

        $perms = $sessionClass::registerSessionParam("permissions", [ "allowed" => $allowed, "revoked" => $revoked ] );
        $instance->permissions = $perms["allowed"];
        $instance->revokes = $perms["revoked"];

    }

    public function flattenPermissions ($reset = false)
    {

        $instance = self::getInstance();

        if ( $reset )
            $instance->_permissions = false;
        if (!$instance->_permissions) {
            $instance->_permissions = [];
            foreach ($instance->permissions as $kp => $permission) {

                $found = false;
                foreach ($instance->roles as $kr => $role) {

                    $matched = false;
                    $matched1 = false;
                    $matched2 = false;

                    // First Level of roles to flatten
                    if (is_array($role)) {

                        if ($kr == $permission) {

                            if ( isset($instance->revokes[$permission])) {
                                continue;
                            }
                            $instance->_permissions[$permission] = $permission;
                            $matched = true;
                            $found = true;
                        }

                        $matched1 = false;
                        foreach ($role as $kr1 => $role1) {

                            $matched2 = false;

                            // Second Level of roles to flatten
                            if (is_array($role1)) {
                                if ($kr1 === $permission) {
                                    if ( isset($instance->revokes[$permission])) {
                                        continue;
                                    }
                                    $instance->_permissions[$permission] = $permission;
                                    $matched1 = true;
                                    $found = true;
                                }

                                $matched2 = false;
                                foreach ($role1 as $kr2 => $role2) {

                                    if ( isset($instance->revokes[$role2])) {
                                        continue;
                                    }
                                    if (is_string($role2)) {

                                        if ($kr2 == $permission) {
                                            $matched2 = true;
                                            $found = true;
                                        }

                                        if ($matched || $matched1 || $matched2){
                                            if ( isset($instance->revokes[$role2])) {
                                                continue;
                                            }
                                            $instance->_permissions[$role2] = $role2;
                                        }
                                    } else {
                                        echo "Max 3 levels of permissions";
                                        die;
                                    }
                                }
                                $matched2 = false;
                            } else
                                if ($role1 === $permission) {
                                    $matched2 = true;
                                }
                            if ($matched || $matched2) {
                                if ( !isset($instance->revokes[$role1])){
                                    $instance->_permissions[$role1] = $role1;
                                } else {
                                }
                            }
                        }
                        $matched1 = false;
                    } else
                        if ($kr === $permission) {
                            $matched = true;
                        }
                    if ($matched){
                        $instance->_permissions[$kr] = $kr;
                    }
                }

                //if ( $matched )
                    //echo "<BR> ====$permission = $found<BR>";

                if ( !$found ) {
                    $instance->_permissions[$permission] = $permission;
                }
            }
            $sessionClass = \Reportico\Engine\ReporticoSession();
        }

    }

    public static function _allowed ( $action ) {

        $instance = self::getInstance();

        $instance->flattenPermissions();
        //echo $action."<BR>"; var_dump($instance->permissions);
        if ( in_array($action, $instance->_permissions) && !in_array($action, $instance->revokes))
            return true;
        else {
            //echo "===================flag $action".in_array($action, $instance->flags)."<BR>";
            //var_dump($instance->flags);
            if (in_array($action, $instance->flags))
                return true;
            else
                return false;
        }
    }

    public static function show ($msg = "")
    {

        $instance = self::getInstance();
        $instance->flattenPermissions();

        if ( $msg )
            echo "$msg: ";
        echo "Permissions (";
        foreach( $instance->_permissions as $permission ) {
            echo $permission.",";
        }
        echo ")<BR>";

        echo "Revokes (";
        foreach( $instance->revokes as $permission ) {
            echo $permission.",";
        }
        echo ")<BR>";

        echo "Flags (";
        foreach( $instance->flags as $permission ) {
            echo $permission.",";
        }
        echo ")<BR>";

    }


    public static function widgetRenders ($type = false)
    {

        //echo "<BR><BR>RENDERS<BR>";
        //self::show();
        $instance = self::getInstance();
        $instance->flattenPermissions();

        //die;

        $renders = [ "permissions" => [], "flags" => [] ];
        foreach( $instance->_permissions as $permission ) {
            $renders["permissions"][$permission] = $permission;
        }

        //foreach( $instance->revokes as $permission ) {
            //echo "REVOKE $permission<BR>";
            //if ( isset($renders["permissions"][$permission])) {
                //unset($renders["permissions"][$permission]);
                //echo "UNSET $permission<BR>";
            //}
        //}

        foreach( $instance->flags as $flag ) {
            $renders["flags"][$flag] = $flag;
        }

        if ( $type){
            return $renders[$type];
        }
        return $renders;
    }


    public static function saveToSession () {

        $instance = self::getInstance();
        $sessionClass = \Reportico\Engine\ReporticoSession();
        $sessionClass::setReporticoSessionParam ("permissions", [ "allowed" => $instance->permissions, "revoked" => $instance->revokes ] );

    }

    /**
     * Get the instance of the chosen Authenticator
     *
     * @return SingletonClass
     */
    public static function getInstance()
    {
        if (true === is_null(self::$_instance)) {
            ReporticoApp::backtrace();
            echo "Class Authenticator not initialized";
            die;
        }
        return self::$_instance;
    }

    public static function initialize ($class, $engine) {

        if (true === is_null(self::$_instance)) {
            self::$_instance = new $class($engine);
        }

        $instance = self::getInstance();
        $instance->engine = $engine;

        Authenticator::resumeFromSession($instance->_defaultPermissions, $instance->_defaultRevokes);

        //$instance->show("====================================<BR>INITIALIZE");

        return $instance;
    }

    public function _login () {

        $this->role = self::ADMIN_ROLE;
        return true;

    }

}
