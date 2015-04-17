<?php
	require_once('../lib/nusoap.php');
	
	$soapcliente=new nusoap_client('http://www.silav.esy.es/WebService/servicio.php');
	$res=$soapcliente->call('notificarEstadoPasajeEnCurso',
										array('idPasaje'=>'20145','estado'=>'asignado'));
										
	print_r($res, $return = null);
	 									
?>