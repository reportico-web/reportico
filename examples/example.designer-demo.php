<?php

      require_once(__DIR__ .'/../vendor/autoload.php');

      \Reportico\Engine\Builder::build()
          ->accessLevel("demo")
          ->project("tutorials")
          ->menu();
?>
