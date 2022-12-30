<?php
namespace Reportico\Engine;

$menu_title = ReporticoApp::getConfig("project_title");
$menu = array (
        array ( "language" => "en_gb", "report" => "", "title" => "BLANKLINE" ),
	array ( "language" => "en_gb", "report" => "createproject.xml", "title" => "Create A New Project" ),
	array ( "language" => "en_gb", "report" => "createtutorials.xml", "title" => "Configure Tutorials" ),
	);
?>
