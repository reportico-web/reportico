<?php
$g_translations = array (
        "en_gb" => array ( 
            "Specify Project Name" => "Specify Project Name",
            "Specify Project Title" => "Specify Project Title",
            "HELP_PROJECT_NAME" => "The name of the project. A folder with this name will be created under the 'projects' folder within the Reportico Installation Directory",
            "HELP_PROJECT_TITLE" => "A title which will appear at the top of the report suite menu .. i.e. a human-understandable name for the project",
            "HELP_DATABASE_TYPE" => "The type of database you wish to report against. ",
            "HELP_DATABASE_HOST" => "The IP address or the name of the host where the database resides. For a database on the same machine as the webserver use 127.0.0.1.  For SQLite databases leave as the default. For Oracle, Mysql, PostgreSQL databases listening on a non-standard port, you can specify in the form HOSTNAME:PORT or IPADDRESS:PORT",
            "HELP_DATABASE_NAME" => "The name of the database to report against. For SQLite databases enter the full path to the database file.",
            "HELP_DATABASE_USER" => "The user name required to connect to the database",
            "HELP_DATABASE_PASSWORD" => "The password required to connect to the database",
            "HELP_DB_ENCODING" => "The encoding format used to store characters in your database. Accepting the default of None will normally work otherwise UTF8 will work for English speaking regions and most other cases. You can always come back to the configuration page to change it at a later point.",
            "HELP_OUTPUT_ENCODING" => "Default is UTF8 which is normally the best. When outputting data, output will be converted to this format before rendering the browser, PDF viewer etc.",
            "HELP_PASSWORD" => "Choose a password which must users must enter to access the project reports. Leave this blank to allow access to project without a password.",
            "HELP_LANGUAGE" => "Choose the default language this report suite should run in. By default English is the only choice. There are a few other language packs available which you will find under the language/packs folder somewhere below the Reportico plugin folder. Move any required ones to the language folder",
            "HELP_DB_DATE" => "Choose the date format that dates are stored in your database. For Mysql and most other databases, the setting of YYYY-MM-DD is correct",
            "HELP_DATE_FORMAT" => "Choose the date format that you would like to use for displaying and entering dates",
            "HELP_SAFE_MODE" => "When turned on, design mode will prevent entry of custom user code, assignments, and SQL statements (avoiding unwanted entry of dangerous PHP commands and SQL injection ).
Turn this off to enable access to these functions.",
            )
        );


$g_report_desc = array ( 
    "en_gb" => array (
		"createproject" =>
"
Create a new projects folder in which you can create a set of reports.
<br>
You must provide at minimum a project name which is the name of the folder used for creating reports, and a project title for your report suite.
<p>
If you are providing the report suite to users on a web site you might like to password protect access to the reports by setting a report password. Otherwise leave blank.
<P>
When you are happy hit the Go button.
<P>
"),
		);

$g_report_desc["en_gb"]["configureproject"] = 
"
Change configuration items for this project.
<br>
You can alter the title, the default language, set a project password, the character encoding and the date format.
<P>
When you are happy hit the Go button.
<P>
";
?>
