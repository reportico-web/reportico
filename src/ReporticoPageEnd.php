<?php

namespace Reportico\Engine;

/**
 * Class ReporticoPageEnd
 *
 * Handles storage of page footer attributes for PDF report output.
 */
class ReporticoPageEnd extends ReporticoObject
{
    public $text = "";
    public $line = 1;
    public $attributes = array(
        "ColumnStartPDF" => false,
        "justify" => "center",
        "ColumnWidthPDF" => false,
        "ShowInPDF" => "yes",
        "ShowInHTML" => "no",
    );

    public function __construct($line, $text)
    {
        parent::__construct();
        $this->line = $line;
        $this->text = $text;
    }

}
