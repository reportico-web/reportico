<?php

      require_once(__DIR__ .'/../vendor/autoload.php');

      \Reportico\Engine\Builder::build()
          ->project("tutorials")
          ->load("stock")
          ->execute();
?>
