<?php
include (__DIR__."/header.php");

$description = "
<br><br>
Reportico reports can be put inside existing pages just by adding the code inside the page code.
<br><br>
";

$usage_method = "
    <HTML>
        <BODY>
            <DIV> 
                ... Some page content ....
                <?php 
                    \Reportico\Engine\Builder::build()
                        ...
                        {Build the Report}
                        ...
                        ->execute();
                ?>
            </DIV>
        </BODY>
    </HTML>
";



$example_description = "
This example shows Reportico embedded in a bassic page and running within half width div
";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>

