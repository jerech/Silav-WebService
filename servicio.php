<?php

	// Deshabilitar cache
	ini_set("soap.wsdl_cache_enabled", "0");

	require_once('lib/nusoap.php');

	$usuarioDB="u666709690_root";
	$contraseniaDB="js12345";
	$servidorDB="mysql.hostinger.es";
	$nombreDB='u666709690_silav';
		
	$urlns="www.silav.hol.es/Web Service";

	//Se crea el servidor Soap
	$servidor=new soap_server;
	
	//Se configura el WSDL
	$servidor->configureWSDL('WSsilav',$urlns);
	$servidor->wsdl->schemaTargetNamespace=$urlns;
	
	//Se tienen que registrar las funciones que se van a usar

	$servidor->register('conectarChofer',
								array("usuario"=>'xsd:string',"contrasenia"=>'xsd:string',"num_remis"=>'xsd:int',"estado"=>'xsd:string'),
								array("return"=>'xsd:boolean'),
								$urlns);
								
	$servidor->register('actualizarEstado',
								array("estado"=>'xsd:string',"usuario"=>'xsd:string'),
								array("return"=>'xsd:boolean'),
								$urlns);
								
	$servidor->register('actualizarUbicacion',
								array("usuario"=>'xsd:string',"ulatitud"=>'xsd:string',"ulongitud"=>'xsd:string'),
								array("return"=>'xsd:boolean'),
								$urlns);
								
 	$servidor->register('desconectarChofer',
 								array("usuario"=>'xsd:string',"num_remis"=>'xsd:int'),
 								array("return"=>'xsd:boolean'),
 								$urlns);
 								
 	$servidor->register('mensajeSos',
 								array("usuario"=>'xsd:string'),
 								array("return"=>'xsd:boolean'),
 								$urlns);
 								
 	$servidor->register('obtenerRemises',
 								array("usuario"=>'xsd:string'),
 								array("return"=>'tns:ArregloRemises'),
 								$urlns);
								
								
	//Se agregan las estructuras de datos necesarias
	
	$servidor->wsdl->addComplexType(
									'Remis',
									'complexType',
									'struct',
									'all',
									'',
									 array(
                        			'numero'            => array('name' => 'numero', 'type' => 'xsd:int'),
                        			'marca'            => array('name' => 'marca', 'type' => 'xsd:string'),
                        			'modelo'       => array('name' => 'modelo', 'type' => 'xsd:string' ),
                            ));
	$servidor->wsdl->addComplexType('ArregloRemises',
												'complexType',
												'array',
												'',
												'SOAP-ENC:Array',
												 array(),
											    array(array('ref' => 'SOAP-ENC:arrayType',
											         'wsdl:arrayType' => 'tns:Remis[]')
											        ),
											    'tns:Remis');
								
	//Implementación de las funciones necesarias
	function conectarChofer($usuario, $contrasenia, $num_remis, $estado) {
			global $usuarioDB,$contraseniaDB,$servidorDB,$nombreDB;
			$consultaOk = false;
			$com=mysql_connect($servidorDB, $usuarioDB, $contraseniaDB); 
			if(!$com){
				die('No se pudo conectar:'.mysql_error());
							
			}
			
			$bd_seleccionada=mysql_select_db($nombreDB);
			if(!$bd_seleccionada){
				die('No se puede usar '.$nombreDB.':'.mysql_error());			
			}
			
			$consultaEsChoferRegistrado="select id, usuario from Choferes where usuario='$usuario' and contrasenia='$contrasenia'";
			$consultaChoferEstaConectado = "select usuario from ChoferesConectados where usuario='$usuario'";			
			$consultaConectarChofer = "insert into ChoferesConectados(usuario,numero_remis,estado_remis) values ('$usuario','$num_remis','$estado')";
			//$resultado=mysql_query($consulta, $com) or die("Problemas en el select:".mysql_error());
			$totalCampos=mysql_num_rows(mysql_query($consultaEsChoferRegistrado));
			
			if($totalCampos==1){
				
				if(mysql_num_rows(mysql_query($consultaChoferEstaConectado)) == 0){
					$consultaOk = mysql_query($consultaConectarChofer);
					
				}else {
					$consultaOk=false;
					
				}
			}else {
				$consultaOk = false;
			}
		
			mysql_close($com);
		
			return $consultaOk;
						
		}
	
	function actualizarEstado($estado,$usuario) {
		global $usuarioDB,$contraseniaDB,$servidorDB,$nombreDB;

			$com=mysql_connect($servidorDB, $usuarioDB, $contraseniaDB); 
			if(!$com){
				die('No se pudo conectar:'.mysql_error());
							
			}
			
			$bd_seleccionada=mysql_select_db($nombreDB);
			if(!$bd_seleccionada){
				die('No se puede usar '.$nombreDB.':'.mysql_error());			
			}
		
		$consulta="update ChoferesConectados set estado_remis='$estado' where usuario='$usuario'";
		$consultaOk=mysql_query($consulta);
		mysql_close($com);
		
		return $consultaOk;
		
	}


	function actualizarUbicacion($usuario, $ulatitud, $ulongitud) {
		
		global $usuarioDB,$contraseniaDB,$servidorDB,$nombreDB;
		$com=mysql_connect($servidorDB, $usuarioDB, $contraseniaDB); 
			if(!$com){
				die('No se pudo conectar:'.mysql_error());
							
			}
		$bd_seleccionada=mysql_select_db($nombreDB);
			if(!$bd_seleccionada){
				die('No se puede usar '.$nombreDB.':'.mysql_error());			
			}
		
		$consulta="update ChoferesConectados set ubicacion_lat='$ulatitud', ubicacion_lon='$ulongitud' where usuario='$usuario'";
		$consultaOk=mysql_query($consulta);
		mysql_close($com);
		
		return $consultaOk;
	}

function desconectarChofer($usuario, $num_remis) {
		global $usuarioDB,$contraseniaDB,$servidorDB,$nombreDB;
		$com=mysql_connect($servidorDB, $usuarioDB, $contraseniaDB); 
			if(!$com){
				die('No se pudo conectar:'.mysql_error());
							
			}
		$bd_seleccionada=mysql_select_db($nombreDB);
			if(!$bd_seleccionada){
				die('No se puede usar '.$nombreDB.':'.mysql_error());			
			}
		$consultaEliminarChoferConectado = "delete from ChoferesConectados where usuario='$usuario' and numero_remis='$num_remis'";
		
		$consultaOk=mysql_query($consultaEliminarChoferConectado);
		mysql_close($com);
		
		return $consultaOk;

}

function obtenerRemises($usuario) {
	global $usuarioDB,$contraseniaDB,$servidorDB,$nombreDB;
		$com=mysql_connect($servidorDB, $usuarioDB, $contraseniaDB); 
			if(!$com){
				die('No se pudo conectar:'.mysql_error());
							
			}
		$bd_seleccionada=mysql_select_db($nombreDB);
			if(!$bd_seleccionada){
				die('No se puede usar '.$nombreDB.':'.mysql_error());			
			}
		
		$consultaRemisesChofer = "SELECT Remises.numero, Remises.marca, Remises.modelo FROM Remises INNER JOIN AsignacionesRemis ON Remises.id = AsignacionesRemis.id_remis INNER JOIN Choferes ON AsignacionesRemis.id_chofer = Choferes.id WHERE Choferes.usuario='$usuario' order by Remises.numero";
		
		$resultado = mysql_query($consultaRemisesChofer);
		$c=0;
		while($registro=mysql_fetch_array($resultado)) {
				$remises[$c]['numero']=$registro[0];
				$remises[$c]['marca']=$registro[1];
				$remises[$c]['modelo']=$registro[2];
				$c++;
			}
		return $remises;
}

function mensajeSos($usuario) {
	global $usuarioDB,$contraseniaDB,$servidorDB,$nombreDB;
		$com=mysql_connect($servidorDB, $usuarioDB, $contraseniaDB); 
			if(!$com){
				die('No se pudo conectar:'.mysql_error());
							
			}
		$bd_seleccionada=mysql_select_db($nombreDB);
			if(!$bd_seleccionada){
				die('No se puede usar '.$nombreDB.':'.mysql_error());			
			}
		
		$consulta = "update ChoferesConectados set sos=true where usuario='$usuario'";
		$consultaOk=mysql_query($consulta);
		mysql_close($com);
		
		return $consultaOk;
}
	
	$servidor->service($HTTP_RAW_POST_DATA);
	

?>