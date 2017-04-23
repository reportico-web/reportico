<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../swutil.php');

class reportico_test extends TestCase
{
    /**
     * Test of the get_template_path function 
     * 
     * @param string $template name of the template
     * 
     * @dataProvider provider_test_get_template_path
     */
    public function test_get_template_path($theme,$template, $expected)
    {
        $q = new \Reportico\reportico();
        $q->setTheme($theme);
        $result = $q->get_template_path($template);
        $this->assertEquals($expected, $result);
    }

    public function provider_test_get_template_path()
    {
        return [
            ['','admin.tpl', 'default/admin.tpl'],
            ['default', 'admin.tpl','default/admin.tpl']
        ];
    }
}
?>