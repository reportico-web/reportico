<?php

      require_once(__DIR__ .'/../vendor/autoload.php');

      \Reportico\Engine\Builder::build()
          ->properties(["url_path_to_assets" => "../assets"])
          ->properties(["url_path_to_templates" => "../themes"])
          ->project("tutorials")
          ->load("stock")
          ->execute();
?>
