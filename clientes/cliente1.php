<?php
	require_once('lib/nusoap.php');
	
	$soapcliente=new nusoap_client('http://www.silav.hol.es/Web Service/servicio.php');
	$res=$soapcliente->call('obtenerRemises',
										array('usuario'=>'root'));
										
	foreach($res as $row){
		echo $row['numero'].', '.$row['marca'].', '.$row['modelo'].'<br>';
	}
	 									
?>