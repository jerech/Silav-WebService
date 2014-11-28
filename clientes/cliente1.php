<?php
	require_once('lib/nusoap.php');
	
	$soapcliente=new nusoap_client('http://www.silav.esy.es/WebService/servicio.php');
	$res=$soapcliente->call('obtenerMoviles',
										array('usuario'=>'jerech'));
										
	foreach($res as $row){
		echo $row['numero'].', '.$row['marca'].', '.$row['modelo'].'<br>';
	}
	 									
?>