<?php

	set_include_path("../../");
	require_once ('../../nusoap.php' );
	//require_once('../../config.php');
	require_once('../../reportico.php');
	error_reporting(E_ALL);

	function {$WS_SERVICE_CODE}($in_soap_param)
{literal}
	{
		//var_dump($in_soap_param);

		$a = new reportico_query();
		$a->reports_path = ".";
		//$a->allow_maintain = true;
		//$a->allow_debug = true;
{/literal}
		$_REQUEST["project"] = "{$PROJECT}";
{literal}
		$_REQUEST["target_format"] = "SOAP";
		$_REQUEST["target_show_body"] = 1;
		$_REQUEST["execute_mode"] = "EXECUTE";
		$_REQUEST["xmlin"] = $in_soap_param["ReportXML"];

		foreach ( $in_soap_param as $k => $v )
		{
			$_REQUEST["MANUAL_".$k] = $v;
		}
		$a->execute($a->get_execute_mode(), true);
		return ($a->targets[0]->soapresult);
	}
{/literal}


	$server = new soap_server("{$WS_SERVICE_BASEURL}/projects/{$PROJECT}/{$WS_SERVICE_CODE}.wsdl");

	$HTTP_RAW_POST_DATA = 
			isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
	$server->service($HTTP_RAW_POST_DATA);

{literal}
	//if(isset($log) and $log != '')
	//{
   		//harness('nusoap_r2_base_server',
			//$server->headers['User-Agent'],
			//$server->methodname,
			//$server->request,
			//$server->response,
			//$server->result);
	//}
{/literal}

?>
