<?php
namespace Reportico\Engine;

//global $_configure_mode;

// Extract Criteria Options
$configparams = array();

global $g_system_errors;

$g_system_errors = array();
global $g_debug_mode;
if ( $_configure_mode != "DELETE" )
{
    $configparams["SW_PROJECT_PASSWORD"] = $_criteria["projectpassword"]->getCriteriaValue("VALUE", false);
    $configparams["SW_DB_TYPE"] = $_criteria["dbtype"]->getCriteriaValue("VALUE", false);
    $configparams["SW_DB_DATABASE"] = $_criteria["database"]->getCriteriaValue("VALUE", false);
    $configparams["SW_DB_HOST"] = $_criteria["host"]->getCriteriaValue("VALUE", false);
    $configparams["SW_DB_SERVER"] = $_criteria["server"]->getCriteriaValue("VALUE", false);
    $configparams["SW_DB_USER"] = $_criteria["user"]->getCriteriaValue("VALUE", false);
    $configparams["SW_DB_PASSWORD"] = $_criteria["password"]->getCriteriaValue("VALUE", false);
    $configparams["SW_DB_PROTOCOL"] = $_criteria["protocol"]->getCriteriaValue("VALUE", false);
    $configparams["SW_HTTP_BASEDIR"] = $_criteria["baseurl"]->getCriteriaValue("VALUE", false);
    $configparams["SW_PROJECT"] = $_criteria["project"]->getCriteriaValue("VALUE", false);
    $configparams["SW_PROJECT_TITLE"] = $_criteria["projtitle"]->getCriteriaValue("VALUE", false);
    if ( $_configure_mode == "CREATE" )
        $configparams["SW_SAFE_DESIGN_MODE"] = true;
    else
        $configparams["SW_SAFE_DESIGN_MODE"] = $_criteria["safemode"]->getCriteriaValue("VALUE", false);

    $configparams["SW_DB_DATEFORMAT"] = $_criteria["dbdateformat"]->getCriteriaValue("VALUE", false);
    $configparams["SW_PREP_DATEFORMAT"] = $_criteria["displaydateformat"]->getCriteriaValue("VALUE", false);
    $configparams["SW_DB_ENCODING"] = $_criteria["dbencoding"]->getCriteriaValue("VALUE", false);
    $configparams["SW_OUTPUT_ENCODING"] = $_criteria["outputencoding"]->getCriteriaValue("VALUE", false);
    $configparams["SW_LANGUAGE"] = $_criteria["language"]->getCriteriaValue("VALUE", false);

    if ( !$configparams["SW_DB_TYPE"] ) { trigger_error ( "Specify Database Type", E_USER_NOTICE ); return; }

    $test = new \Reportico\Engine\reporticoDatasource();
    $test->driver = $configparams["SW_DB_TYPE"];

    if ( $test->driver != "framework" && $test->driver != "existingconnection" )
    {
        if ( !$configparams["SW_DB_DATABASE"] ) { trigger_error ( "Specify Database Name", E_USER_NOTICE ); return; }
        if ( !$configparams["SW_DB_USER"]  && $configparams["SW_DB_TYPE"] != "pdo_sqlite3" ) { trigger_error ( "Specify Database User", E_USER_NOTICE ); return; }
        if ( !$configparams["SW_DB_HOST"] ) { trigger_error ( "Specify Database Host", E_USER_NOTICE ); return; }
    }

    if ( !$configparams["SW_PROJECT"] ) { trigger_error ( "Specify Project Name", E_USER_NOTICE ); return; }
    if ( !$configparams["SW_PROJECT_TITLE"] ) { trigger_error ( "Specify Project Title", E_USER_NOTICE ); return; }
    //if ( !$configparams["SW_HTTP_BASEDIR"] ) { trigger_error ( "Specify Base URL", E_USER_NOTICE ); return; }

    $g_debug_mode = true;
    ReporticoApp::set("no_sql",true);

    if ( $test->driver == "existingconnection" || preg_match("/^byname_/", $test->driver))
    {
        $configparams["SW_DB_USER"] = "N/A";
        $configparams["SW_DB_PASSWORD"] = "N/A";
        $configparams["SW_DB_HOST"] = "N/A";
        $configparams["SW_DB_DATABASE"] = "N/A";
        $configparams["SW_DB_SERVER"] = "N/A";
        $configparams["SW_DB_PROTOCOL"] = "N/A";
    }
    else if ( $test->driver == "framework" )
    {
        $configparams["SW_DB_USER"] = "N/A";
        $configparams["SW_DB_PASSWORD"] = "N/A";
        $configparams["SW_DB_HOST"] = "N/A";
        $configparams["SW_DB_DATABASE"] = "N/A";
        $configparams["SW_DB_SERVER"] = "N/A";
        $configparams["SW_DB_PROTOCOL"] = "N/A";
    }
    else
    {
        $test->user_name = $configparams["SW_DB_USER"];
        $test->password = $configparams["SW_DB_PASSWORD"];
        $test->host_name = $configparams["SW_DB_HOST"];
        $test->database = $configparams["SW_DB_DATABASE"];
        $test->server = $configparams["SW_DB_SERVER"];
        $test->protocol = $configparams["SW_DB_PROTOCOL"];

        $test->connect(true);
        if ( $test->connected )
        {
            ReporticoApp::handleDebug("Connection to Database succeeded", 0);
        }
        else
        {
            trigger_error("Connection to Database failed", E_USER_NOTICE);
            return;
        }
    }

}
else
{
    $configparams["SW_PROJECT"] = $_criteria["project"]->getCriteriaValue("VALUE", false);
    $configparams["SW_PROJECT_TITLE"] = $_criteria["projtitle"]->getCriteriaValue("VALUE", false);
}

$proj_parent = $this->projects_folder;
if (  !is_dir($proj_parent) )
    $proj_parent = find_best_location_in_include_path( $this->projects_folder );

$admin_folder = $this->admin_projects_folder;
if (  !is_dir($admin_folder) )
    $admin_folder = find_best_location_in_include_path( $this->admin_projects_folder );

$proj_dir = $proj_parent."/".$configparams["SW_PROJECT"];
$proj_conf = $proj_dir."/config.php";
$proj_menu = $proj_dir."/menu.php";
$proj_lang = $proj_dir."/lang.php";

$proj_template = $admin_folder."/admin/config.template";
$menu_template = $admin_folder."/admin/menu.template";
$lang_template = $admin_folder."/admin/lang.template";


if ( !file_exists ( $proj_parent ) )
{
    trigger_error ("Projects area $proj_parent does not exist - cannot write project", E_USER_NOTICE);
    return;
}

if ( !is_writeable ( $proj_parent  ) )
{
    if ( $_configure_mode == "DELETE" )
        trigger_error ("Projects area $proj_parent is not writeable - cannot delete project", E_USER_NOTICE);
    else
        trigger_error ("Projects area $proj_parent is not writeable - cannot write project", E_USER_NOTICE);
    return;
}

// In framework systems, creating the tutorials involves copying the existing project over
if ( $admin_folder != $proj_parent && $_configure_mode == "CREATETUTORIALS" )
{
    $source_dir = "$admin_folder/tutorials";
    if ( file_exists($proj_dir) && file_exists($proj_conf) )
    {
        //trigger_error("Tutorials folder $source_dir already exists which means the tutorials are already there", E_USER_NOTICE);
        //return;
        unlink($proj_conf);
    }
    $source_config = "$admin_folder/tutorials/config.php";
    if ( file_exists($proj_dir) && !file_exists($proj_conf) )
    {
        copy($source_config, $proj_conf);
        trigger_error ("Tutorials created successfully", E_USER_NOTICE);
        return;
    }

    if ( !is_writeable ( $proj_parent  ) )
    {
        trigger_error ("Projects area $proj_parent is not writeable - cannot create tutorials there", E_USER_NOTICE);
        return;
    }

    // Copy whole project recursively
    $dir = opendir($source_dir); 
    $dst = $proj_dir;
    mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            copy($source_dir . '/' . $file,$dst . '/' . $file);
        } 
    } 
    closedir($dir); 
    trigger_error ("Tutorials created successfully", E_USER_NOTICE);
    return;
    
}

if ( file_exists ( $proj_dir ) )
{
    if ( $_configure_mode == "CREATE" )
    {
        trigger_error ("Projects area $proj_dir already exists - cannot write project - use Configure Project from the administration menu to change it. ", E_USER_NOTICE);
    	return;
    }
}
else 
if ( $_configure_mode != "CREATE" )
{
        trigger_error ("Unable to access project. Projects area $proj_dir does not exist - if you are trying to rename the project, then rename the project folder manually", E_USER_NOTICE);
    	return;
}
else
    if ( !mkdir ( $proj_dir ) )
    {
        trigger_error ("Failed to create project directory $proj_dir", E_USER_NOTICE);
        return;
    }

if ( !is_writeable ( $proj_dir ) )
{
   if ( ! chmod ( $proj_dir, "0755") )
   {
        trigger_error ("Failed to make project directory $proj_dir writeable ", E_USER_NOTICE);
   }
}

if ( !file_exists ( $proj_conf ) && $_configure_mode == "DELETE" )
{
    trigger_error ("Projects configuration file $proj_conf not found. Project already deleted/deactivated", E_USER_NOTICE);
    return;
}

if ( file_exists ( $proj_conf ) && $_configure_mode == "DELETE" )
{
    if ( !($status = rename ( $proj_conf, $proj_conf.".deleted" )) )
        trigger_error ("Failed to disable $proj_conf file. Possible permission, configuration problem", E_USER_NOTICE);
    else
	    ReporticoApp::handleDebug("Project Deleted Successfully", 0);
    ReporticoApp::set("no_sql",true);
    
    return;
}

if ( file_exists ( $proj_conf ) && !is_writeable($proj_conf) )
{
    trigger_error ("Projects configuration file $proj_conf exists but is not writeble. Cannot continue", E_USER_NOTICE);
    return;
}

if ( $_configure_mode == "CREATE" || $_configure_mode == "CREATETUTORIALS" )
{
	$txt = file_get_contents($proj_template);
}
else
{
    $conffound = false;
	if ( file_exists ( $proj_conf ) )
    {
		$txt = file_get_contents($proj_conf);

	    if ( !preg_match("/ReporticoApp/", $txt)) {
            ReporticoApp::handleError ("Warning - This project was created with an older version of reportico. The configuration file has been replaced with a new version compatible with $this->version. Your original config file was backed up in the project folder to config.php.orig.", E_USER_WARNING);
            $proj_menu = $proj_dir."/menu.php";
            $retval = file_put_contents($proj_conf.".orig", $txt);

            // We dont need a menu.php file in the new version
            if ( file_exists($proj_menu) )
                rename($proj_menu, $proj_menu.".orig");
        }
        // If the config file does not have use ReporticoApp for storing config
        // or SW_DB_TYPE entry then we are running a pre-2.8
        // report with a post 2.8 reportico ... so generate a new one from the admin template
        else if ( preg_match("/ReporticoApp/", $txt ) || preg_match ( "/SW_DB_TYPE/", $txt ))
        {
            $conffound = true;
        }
        else
        {
            if ( $configparams["SW_DB_TYPE"] == "framework" )
            {
                ReporticoApp::handleDebug ("Warning - This project was created with an older version of reportico which cannot use the connection details of an application framework. In order to connect to a framework the project configuration file ".$configparams["SW_PROJECT"]."/config.php was updated. Any manually made modifications are saved as the original config.php was backed up to the file config.php.orig.", 0);
	            $retval = file_put_contents($proj_conf.".orig", $txt);
            }
        }
    }

    if ( !$conffound )
    {
		if ( file_exists ( $proj_template ) )
			$txt = file_get_contents($proj_template);
		else
		{
    			trigger_error ("Cannot find source $proj_conf or $proj_template to configure", E_USER_NOTICE);
    			return;
		}
    }
}

$matches = array();


//if ( $configparams["SW_DB_TYPE"] == "framework" )
//{
        //ReporticoApp::handleDebug("Connection to Database not checked as framework database connections have been used", 0);
//}

// If this is a reportico pre 2.8 then it wont handle "framework" type
$installation_type = "OLD";
foreach ( $configparams as $paramkey => $paramval )
{
	if ( $paramkey == "SW_PROJECT" ) 
		continue;

    // Dont allow config parameter of quotes to escape them to avoid injection
    // This means we do not want multiple backslashes
    $paramval = preg_replace('/[\\\][\\\]*/', '\\', $paramval);
    $paramval = preg_replace('/\\\$/', '\\\\\\', $paramval);
    $paramval = addcslashes($paramval, "'\"");

	if ( !preg_match("/ReporticoApp/", $txt)) {
        // Apply config to older types of config file stored as defines
	    $match = preg_match ( "/(define.*?$paramkey',).*\);/", $txt);
        if ( $match ) {
            if ( $paramkey == "SW_SAFE_DESIGN_MODE" )
            {
                if ( $paramval )
                    $paramval = "true";
                else
                    $paramval = "false";
                $txt = preg_replace ( "/(define.*?$paramkey',).*\);/", "$1$paramval);", $txt);
            }
            else
            {
                $paramval = $paramval;
                $txt = preg_replace ( "/define\('$paramkey', *'.*'\);/", "define('$paramkey', '$paramval');", $txt);
            }
        }
        continue;
    }

    $installation_type = "NEW";

    // Use new type of
    $modkey = strtolower($paramkey);
    $modkey = preg_replace("/^sw_/", "", $modkey);


    // Check if parameter exists in config file and if not add it (caters for new parameters in Reportico for existing projects )
	$match = preg_match ( "/ReporticoApp::setConfig\(.$modkey.,/", $txt);
    if ( !$match ) {
	    $txt = preg_replace ( "/\?>/", "\n// Automatic addition of parameter $modkey\nReporticoApp::setConfig('$modkey','$paramval');\n?>", $txt);
    }
    else {
        if ( $paramkey == "SW_SAFE_DESIGN_MODE" ) {
            if ( $paramval )
                $paramval = "true";
            else
                $paramval = "false";
    
            $txt = preg_replace ( "/(ReporticoApp::setConfig\(.$modkey.,).*/", "$1$paramval);", $txt);
        }
        else {
            $paramval = $paramval;
            $txt = preg_replace ( "/(ReporticoApp::setConfig\(.$modkey.,) *[\"'].*/", "$1\"$paramval\");", $txt);
        }
    }
}

$retval = file_put_contents($proj_conf, $txt);

if ( $_configure_mode == "CREATE" )
{
    if ( $installation_type == "OLD" )
    {
	    $txt = file_get_contents($menu_template);
	    $retval = file_put_contents($proj_menu, $txt);
    }
	$txt = file_get_contents($lang_template);
	$retval = file_put_contents($proj_lang, $txt);
}

if ( $configparams["SW_PROJECT"] != "tutorials" )
if ( !$configparams["SW_PROJECT_PASSWORD"] ) ReporticoApp::handleDebug ("Warning - Project password not set - any user will be able to run reports in this project", 0);

if ( $_configure_mode == "CREATETUTORIALS" )
	ReporticoApp::handleDebug("Tutorials Project Created Successfully, now use the \"Generate the Tutorial Tables\" option to create and populate the tutorial tables", 0);
else if ( $_configure_mode == "CREATE" )
	ReporticoApp::handleDebug("Project Created Successfully", 0);
else
	ReporticoApp::handleDebug("Project Configuration Updated Successfully", 0);

$g_debug_mode = false;





?>
