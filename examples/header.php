<html>
    <!--link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous"-->
    <!-- Our Custom CSS -->
    <link rel="stylesheet" href="../assets/bootstrap4/bootstrap.css">
    <link rel="stylesheet" href="style.css">
    <script src="../assets/node_modules/jquery/js/jquery.js"></script>
    <script src="../assets/bootstrap4/bootstrap.js"></script>


<?php
    $section = isset($_REQUEST["section"]) ? $_REQUEST["section"] : "home";
    $page = preg_replace("/\..*/", "", basename($_SERVER["PHP_SELF"]));
    $page = isset($_REQUEST["page"]) ? $_REQUEST["page"] : $page;

    $menus = [
        "introduction" => [
            "usage_method" => false,
            "title" => "Introduction",
        ],
        "syntax-overview" => [
            "usage_method" => false,
            "title" => "Getting Started",
        ],
        "basics" => [
            "title" => "Basics",
            "items" => [
                "array-report" => [ "usage_method" => "array-report", "title" => "Array Report" ],
                "sql-report" => [ "usage_method" => "sql-report", "title" => "SQL Report" ],
                "columns" => [ "usage_method" => "columns" ],
                "criteria" => [ "usage_method" => "criteria" ],
                "expressions" => [ "usage_method" => "expression" ],
                "groups" => [ "usage_method" => "group" ],
                "charts" => [ "usage_method" => "chart" ],
                "pages" => [ "usage_method" => "page" ],
                "dynamic-tables" => [ "usage_method" => "dynamic-tables", "title" => "Dynamic Tables" ],
                "themes" => [ "usage_method" => "themes", "title" => "Themes" ],
                "embedding" => [ "usage_method" => "themes", "title" => "Embedding in a Page" ],
            ]
        ],
        "output-formats" => [
            "title" => "Output Formats",
            "items" => [
                "output-csv" => [ "title" => "CSV Output" ],
                "output-pdf" => [ "title" => "PDF Output" ],
            ]
        ],
        "features" => [
            "title" => "Features",
            "items" => [
                "features-dropdown-menu" => [ "title" => "Dropdown Menus" ],
                "features-drilldown" => [ "title" => "Drilldown" ],
                "features-form-layout" => [ "title" => "Form Layout" ],
                "features-hide-sections" => [ "title" => "Hide Sections" ],
                "features-relay" => [ "title" => "Passing Values to Report" ],
                "features-relay-criteria" => [ "title" => "Passing Criteria to Report" ],
                "features-styling" => [ "title" => "Styling", "usage_method" => "expression"  ],
            ]
        ],
        "projects" => [
            "title" => "Projects",
            "items" => [
                "project-menu" => [ "title" => "Show a Report Project Menu" ],
                "project-prepare" => [ "title" => "Project Report Criteria" ],
                "project-execute" => [ "title" => "Run a Project Report" ],
            ]
        ],
        "designer-overview" => [
            "title" => "Report Designer",
        ],
    ];

    foreach ( $menus as $kmenu => $menu ) {
        if ( !isset($menu["items"] )){
            if ( !isset($menu["url"] )) {
                $menus[$kmenu]["url"] = $kmenu.".php";
            }
            if ( !isset($menu["file"] )) {
                $menus[$kmenu]["file"] = "example.".$kmenu.".php";
            }
            if ( !isset($menu["example_url"] )) {
                $menus[$kmenu]["example_url"] = "example.".$kmenu.".php";
            }
            if ( !isset($menu["title"] )) {
                $menus[$kmenu]["title"] = ucwords($kmenu);
            }
            if ( !isset($menu["usage_method"] )) {
                $menus[$kmenu]["usage_method"] = $kmenu;
            }
            if ( $page == $kmenu ) {
                $section = $kmenu;
                $example_file = $menus[$kmenu]["file"];
                $url = $menus[$kmenu]["url"];
                $example_url = $menus[$kmenu]["example_url"];
                $title = $menus[$kmenu]["title"];
                $usage_method = $menus[$kmenu]["usage_method"];
            }
            continue;
        }

        foreach ( $menu["items"] as $kitem => $item ) {
            if ( !isset($item["url"] )) {
                $menus[$kmenu]["items"][$kitem]["url"] = $kitem.".php";
            }
            if ( !isset($item["file"] )) {
                $menus[$kmenu]["items"][$kitem]["file"] = "example.".$kitem.".php";
            }
            if ( !isset($item["example_url"] )) {
                $menus[$kmenu]["items"][$kitem]["example_url"] = "example.".$kitem.".php";
            }
            if ( !isset($item["title"] )) {
                $menus[$kmenu]["items"][$kitem]["title"] = ucwords($kitem);
            }
            if ( !isset($item["usage_method"] )) {
                $menus[$kmenu]["usage_method"][$kitem]["usage_method"] = $kitem;
            }
            if ( $page == $kitem ) {
                $section = $kmenu;
                $example_file = $menus[$kmenu]["items"][$kitem]["file"];
                $url = $menus[$kmenu]["items"][$kitem]["url"];
                $example_url = $menus[$kmenu]["items"][$kitem]["example_url"];
                $title = $menus[$kmenu]["items"][$kitem]["title"];
                $usage_method = $menus[$kmenu]["items"][$kitem]["usage_method"];
            }
        }
    }
?>

<div class="wrapper">

<div>

        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>Reportico Builder</h3>
            </div>

            <ul class="list-unstyled components">

                <?php
                foreach ( $menus as $kmenu => $menu ) {
                    ?>
                    <?php 
                        if ( !isset($menu["items"] )){
                        ?>
                        <li>
                            <a href="<?php echo $menu["url"] ?>?section=<?php echo $kmenu ?>"><?php echo $menu["title"] ?></a>
                        </li>
                        <?php
                            continue;
                        }
                    ?>
                    <li>
                        <a href="#<?php echo $kmenu?>Submenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><?php echo ucwords($menu["title"]); ?></a>
                    <ul class="<?php if ( $section != $kmenu ) echo "collapse"; ?> list-unstyled" id="<?php echo $kmenu?>Submenu">
                    <?php 
                    foreach ( $menu["items"] as $kitem => $item ) {
                    ?>
                        <li>
                            <a href="<?php echo $item["url"] ?>?section=<?php echo $kmenu ?>"><?php echo $item["title"] ?></a>
                        </li>
                    <?php
                    }
                    ?>
                    </ul>
                </li>
                <?php
                }
                ?>
            </ul>
        </nav>

        <div id="content">

<?php
require_once(__DIR__ .'/../vendor/autoload.php');
$code = file_get_contents(__DIR__."/$example_file");
$code = preg_replace("/->datasource.*\)/", "->datasource()->database(\"mysql:host=localhost; dbname=DATABASE NAME\")->user(\"USER\")->password(\"PASSWORD\")", $code);
$code =  highlight_string($code, true);

?>
