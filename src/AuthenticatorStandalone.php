<?php

namespace Reportico\Engine;

/**
 * Class AuthenticatorStandalone
 *
 * Authentication method using the admin project config password to
 * control access to Reportico Design mode
 *
 */


use \Reportico\Engine\ReporticoSession;

class AuthenticatorStandalone extends Authenticator
{
    public function __construct($engine)
    {
        parent::__construct($engine);

        $this->_defaultPermissions = ["guest"];
        $this->_defaultRevokes = [];
    }


    function grantAccessMode() {

        // Set access mode to decide whether to allow user to access Design Mode, Menus, Criteria or just run a single report
        $sessionClass = ReporticoSession();

        if ($this->engine->initial_role && $sessionClass::getReporticoSessionParam("awaiting_initial_defaults")) {
            $sessionClass::registerSessionParam("role", $this->engine->initial_role);
            self::_grant($this->engine->initial_role);
            //return;
        } else {
            $role = $sessionClass::getReporticoSessionParam("role");
            if ( $role ){
                //self::_grant($role);
                return;
            }

        }

        $this->setInitialAccessLevels();
    }


    // On initial load or after logout, initialise or reset grant access based on initial access level
    function setInitialAccessLevels()
    {
        $sessionClass = ReporticoSession();
        $this->engine->access_mode = $sessionClass::sessionItem("access_mode", $this->engine->access_mode);

        if ($this->engine->access_mode == "DEMO") {
            $this->engine->allow_maintain = "DEMO";
        }

        switch ($this->engine->access_mode) {

            case "FULL":
            case "all":
            case "full":
            case "admin":
                self::_reset("admin");
                break;

            case "DEMO":
            case "demo":
            case "design-fiddle":
            case "fiddle":
                self::_reset("design");
                break;

            case "DEMO":
            case "demo":
            case "design-fiddle":
                self::_reset("design-fiddle");
                break;

            case "ALLPROJECTS":
            case "all-projects":
            case "no-admin":
                self::_reset("all-projects");
                break;

            case "ONEPROJECT":
            case "one-project":
            case "project":
            case "single-project":
                self::_reset("project");
                break;

            case "ONEREPORT":
            case "one-report":
            case "REPORT":
            case "report":
                 self::_reset("report");
                 break;

            case "REPORTOUTPUT":
            case "REPORT-OUTPUT":
            case "report-output":
            case "reportoutput":
                 self::_reset("report-output");
                 break;

        }

    }


    public function _login ()
    {

        $sessionClass = ReporticoSession();

        $this->grantAccessMode();

        if (!$this->engine->datasource) {
            $this->engine->datasource = new ReporticoDataSource($this->engine->external_connection, $this->engine->available_connections);

            if ($sessionClass::issetReporticoSessionParam('connection_string') ) {
                $this->engine->datasource->connection_string = $sessionClass::getReporticoSessionParam("connection_string");
                $this->engine->datasource->user_name = $sessionClass::getReporticoSessionParam("user");
                $this->engine->datasource->password = $sessionClass::getReporticoSessionParam("password");
                //$this->engine->report_from_builder = $sessionClass::getReporticoSessionParam("report_from_builder");
            }
        }

        // For builder initiated reports, automatically login 
        if ($this->engine->report_from_builder && !$this->engine->initial_project) {
            //self::_grant("project");
            self::_flag("non-project-operation");
            return true;
        }

        $loggedon = false;

        //self::_revoke("admin-logged-in");
        //self::_revoke("user-logged-in");
        //self::_revoke("admin-password-error");
        //self::_revoke("project-password-error");

        // Logon to admin / design
        $ret = $this->logonAsAdmin();

        //  No admin login - try login to user project access
        if (!$ret )
            $ret = $this->logonAsUser();

        self::saveToSession();

        return $ret;

    }

    function logonAsUser()
    {
        $loggedon = false;

        $sessionClass = ReporticoSession();

        // When drilling down from from a report in a password protected project use that password
        $matches = array();
        if (preg_match("/_drilldown(.*)/", $sessionClass::reporticoNamespace(), $matches)) {
            $parent_session = $matches[1];
            if ($sessionClass::issetReporticoSessionParam("project_password", $parent_session)) {
                $sessionClass::setReporticoSessionParam('project_password', $sessionClass::getReporticoSessionParam("project_password", $parent_session));
            }
        }

        $project_password = ReporticoApp::getConfig("project_password");
        if ( $project_password )
            self::_flag("project-password-protected");
        //echo "LOGON AS USER ." .$sessionClass::getReporticoSessionParam('project_password')."<BR>";
        //echo "PROJECT PASSWORD $project_password <BR>";

        //($this->engine->execute_mode != "MAINTAIN" && $sessionClass::sessionRequestItem('project_password') == $project_password)
        // Access to project if no project password set or correct password entered or admin logged in
        if ( self::_allowed("admin") ) {
            $loggedon = true;
            //echo "already admin - no change<BR>";
        }
        //else if ( self::_allowed("project") ) {
            //$loggedon = true;
            //echo "already project - no change<BR>";
        //}
        else if ( !$project_password ) {
            //echo "no proj password - allow<BR>";
            self::_grant("project");
        }
        else {
            // User has attempted to login .. allow access to report PREPARE and MENU modes if user has entered either project
            // or design password or project password is set to blank. Allow access to Design mode if design password is entered
            // or design mode password is blank
            //echo "try proj password with mode {$this->engine->access_mode} pw {$this->engine->initial_project_password} <BR>";
            if (isset($_REQUEST['project_password']) || $this->engine->initial_project_password) {
//echo "password etered ".$_REQUEST['project_password']."<BR>";
                // Password may have come from external call
                if ($this->engine->initial_project_password) {
                    $testpassword = $this->engine->initial_project_password;
                } else {
                    $testpassword = $_REQUEST['project_password'];
                }

                if ($testpassword == $project_password) {
                    if ( $this->engine->initial_role == "guest" ) {
                        self::_grant("project");
                    }
                    self::_grant("access");
                    //self::_grant("admin-page");
                } else {
                    self::_reset("guest");
                    self::_flag("project-password-error");
                    self::_revoke("access");
                }
            } else {

//echo "password not entered<BR>";
                //self::_revoke("access");
                //self::show();
                if (isset($_REQUEST["login"])) {
                    self::_flag("project-password-error");
                }

            }
        }

        // User has pressed logout button, default then to MENU mode
        if (array_key_exists("logout", $_REQUEST)) {

            //self::_reset("guest");
            $this->setInitialAccessLevels();
            //self::_grant("project");

            //if ($sessionClass::issetReporticoSessionParam("admin_password")) {
                //$sessionClass::unsetReporticoSessionParam('admin_password');
            //}
            $sessionClass::unsetReporticoSessionParam('project_password');
            $sessionClass::setReporticoSessionParam("execute_mode", "MENU");
            $loggedon = false;
            if ($project_password == '') {
                self::_grant("project");
                $loggedon = "NORMAL";
            } else {
                self::_revoke("access");
            }
        }

        // If admin mode or logged on with project password we can show a logout button
        if ( self::_allowed("admin") || ( ( !self::_allowed("design-fiddle") && self::_allowed("access") ) && $project_password ) )  {
            //if ( self::_allowed("admin") )
                //echo "Cond 1 ";
            //if ( ( !self::_allowed("design-fiddle" ) ))
                //echo "Cond 1a ";
            //if ( ( !self::_allowed("design-fiddle" ) || self::_allowed("project") ) && $project_password ) 
                //echo "Cond 2 ";
            //if ( (( self::_allowed("access") && $project_password ))) 
                //echo "Cond 3 ";
            //echo "flag<BR>";
            self::_flag("show-logout-button");
        }

        self::saveToSession();

        return $loggedon;
    }

    function logonAsAdmin() {

        $loggedon = false;

        //echo "<BR><BR>";
        //var_dump($_SESSION["reportico_reportico"]["permissions"]);
        $sessionClass = ReporticoSession();
        if ( ReporticoApp::getConfig("project") == "admin" ) {

            // Allow access to Admin Page if already logged as admin user, or configuration does not contain
            // an Admin Password (older version of reportico) or Password is blank implying site configured with
            // No Admin Password security or user has just reset password to blank (ie open access )
            //self::_grant("guest");

            // Attempt to logout of admin - reset to guest level
            if (array_key_exists("adminlogout", $_REQUEST)) {
                $sessionClass::unsetReporticoSessionParam('admin_password');
                self::_reset("guest");
                $loggedon = false;
            }

            // Admin already logged in do nothing
            if (self::_allowed("admin")) {
                self::_reset("admin");
                return true;
            }

            // Attempt to perform admin login ( admin_password request param set )
            if (array_key_exists("login", $_REQUEST) && isset($_REQUEST['admin_password'])) {
                    // User has supplied an admin password and pressed login
                    if ($_REQUEST['admin_password'] == ReporticoApp::getConfig("admin_password")) {
                        //$sessionClass::setReporticoSessionParam('admin_password', "1");
                        self::_grant("admin");
                        $loggedon = true;
                    } else {
                        // Failed login
                        self::_flag("admin-password-error");
                }
            }

            // If Admin Password is set to blank then force logged on state to true
            if (ReporticoApp::getConfig("admin_password") == "") {
                $sessionClass::setReporticoSessionParam('admin_password', "1");
                self::_reset("admin");
                $loggedon = true;
            }

            return $loggedon;
        }

        return false;
    }

}
