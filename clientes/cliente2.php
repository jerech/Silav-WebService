<?php

  require_once('lib/nusoap.php');
	
	$soapcliente=new nusoap_client("http://silav.hol.es/Web Service/servicio.php");
	
	$res=$soapcliente->call('conectarChofer',
										array("usuario"=>'jerech',"contrasenia"=>'1234',"num_movil"=>2,"estado"=>'LIBRE'));
										
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
	print_r($res);
	echo " Hola:".$res;					
 ?>