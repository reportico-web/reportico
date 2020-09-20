<?php
            require_once(__DIR__ .'/../vendor/autoload.php');
            require_once(__DIR__ .'/data_employees.php');
            \Reportico\Engine\Builder::build()
            ->properties(["url_path_to_assets" => "../assets"])
            ->properties(["url_path_to_templates" => "../themes"])
            ->datasource()->array( $rows )
            ->title("Employee List")
			->execute();
?>
