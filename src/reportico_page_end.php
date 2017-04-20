<?php

namespace Reportico;

/**
 * Class reportico_page_end
 *
 * Handles storage of page footer attributes for PDF report output.
 */
class reportico_page_end extends reportico_object
{
	var	$text = "";
	var	$line = 1;
	var $attributes = array (
		"ColumnStartPDF" => false,
		"justify" => "center",
		"ColumnWidthPDF" => false,
		"ShowInPDF" => "yes",
		"ShowInHTML" => "no",
		);

	function __construct($line, $text)
	{
            parent::__construct();
			$this->line = $line;
			$this->text = $text;
	}

}