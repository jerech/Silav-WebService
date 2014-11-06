<?php

  require_once('lib/nusoap.php');
	
	$soapcliente=new nusoap_client("www.silav.hol.es/Web Service/servicio.php");
	
	// Se pudo conectar?
	$error = $soapcliente->getError();
	if ($error) {
		echo '<pre style="color: red">' . $error . '</pre>';
		echo '<p style="color:red;'>htmlspecialchars($cliente->getDebug(), ENT_QUOTES).'</p>';
		die();
} 
	$res=$soapcliente->call('autenticarChofer',
										array("usuario"=>'jerech'));
										
	if ($soapcliente->fault) {
		echo '<b>Error: ';
		print_r($res);
		echo '</b>';
	} else {
				$error = $soapcliente->getError();
				if ($error) {
					echo '<b style="color: red">-Error: ' . $error . '</b>';
				} else {
					echo $res; 	
					}	
	}						
 ?>