<?php

if ( count($argv) > 1 )
    $compare=$argv[1];
else
    $compare="es_es";
$tmp=array("prepare", "admin", "maintain", "execute", "menu", "languages");

foreach ( $tmp as $template )
{

$fl = $template.".php";

include($fl);

$english = array();
foreach ( $locale_arr["template"] as $k => $v )
{
    $english[$k] = $v;
}

$cfl = "../$compare/$fl";
include($cfl);

echo "$cfl \n\n";

foreach ( $english as $k => $v )
{
    if ( !isset($locale_arr["template"][$k]) )
    {
        echo "            \"$k\" => \"$v\",\n";
    }
}

}


?>
