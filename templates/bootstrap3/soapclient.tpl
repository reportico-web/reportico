<html>
<body>
<?
	include ("../../nusoap.php");

	$report=array
	(
{section name=critno loop=$CRITERIA_ITEMS}
		//'{$CRITERIA_ITEMS[critno].name}' => '???',
{/section}
        	'ReportXML' => "{$WS_REPORTNAME}.xml"
	);

	$client = new soapclient('{$WS_SERVICE_BASEURL}/projects/{$PROJECT}/{$WS_SERVICE_CODE}_wsv.php');

	$returnVal = $client->call("{$WS_SERVICE_CODE}", array($report),
        	'http://{$WS_SERVICE_NAMESPACE}/', 'http://{$WS_REPORTNAME}/' );


{literal}
    	if ($err = $client->getError()) 
	{
		//echo $client->response;
		echo $err;
	}
	else 
	{
		//echo $client->response;
		echo "<br>Report Title <b>".$returnVal["ReportTitle"]."</b>";
		echo "<br>Report Time <b>".$returnVal["ReportTime"]."</b>";
		echo "<TABLE BORDER='1'>";
		$soapval = $returnVal["soapVal"];
		foreach ( $soapval as $k => $v )
		{
			echo "<TR>";
			echo "<TD valign='top'>";
			echo $k;
			echo "</TD>";
			echo "<TD>";
			echo "<TABLE>";
			foreach ( $v as $k1 => $v1 )
			{
				echo "<TR>";
				echo "<TD> ";
				echo $k1;
				echo "</TD>";
				echo "<TD>";
				echo $v1;
				echo "</TD>";
				echo "</TR>";
			}
			echo "</TABLE>";
			echo "</TD>";
			echo "</TR>";
		}
		echo "</TABLE>";
	}
{/literal}
?>
 </body>
</html>
