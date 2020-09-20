<?php
include (__DIR__."/header.php");

$description = "
<br><br>
Reportico provides a theme() method to change the overall look and feel. Reportico contains theme files (stylesheets, HTML templates, javascript etc) under the <b>theme</b> in Reportico. To switch theme just provide this folder name to the theme method.
<br><br>
Reportico comes with two themes bootstrap3 and bootstrap4 (the default used in standalone Reportico and when embedding in frameworks such as Laravel)  but it is possible to copy these to a new theme and change the look and feel. Since Reportico can be embedded in existing web applications and work with existing visual frameworks such as Boostrap, the theme folder defines the framework specific logic for embedding reports. 
<br><br>
For more details on themes and creating your own visit the <a style=\"color:#0000ff; text-decoration: underline\" target=\"_BLANK\" href=\"http://www.reportico.org/documentation/6.0.0/doku.php?id=themes\">Reportico documentation on themes</a>
";

$usage_method = "
<?php
      \Reportico\Engine\Builder::build()
          ...
          ->theme(\"theme_folder\")
          ...
          ->execute();
?>
";


$example_description = "
This example shows a report running in the bootstrap3 theme.
";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>

