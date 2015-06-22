<?php

	// Deshabilitar cache
	ini_set("soap.wsdl_cache_enabled", "0");

	require_once('lib/nusoap.php');
	require_once('../app/conexionBD.php');

	define("archivoINI", "url.ini");
	$array_ini = parse_ini_file(archivoINI, true);
	$urlns=$array_ini['urlns'];

	//Se crea el servidor Soap
	$servidor=new soap_server;
	
	//Se configura el WSDL
	$servidor->configureWSDL('WSsilav',$urlns);
	$servidor->wsdl->schemaTargetNamespace=$urlns;
	
	//Se tienen que registrar las funciones que se van a usar
	
	$servidor->register('hola',
								array("nombre"=>'xsd:string'),
								array("return"=>'xsd:string'),
								$urlns);
								
	$servidor->register('login',
								array("usuario"=>'xsd:string',"contrasenia"=>'xsd:string'),
								array("return"=>'xsd:boolean'),
								$urlns);

	$servidor->register('conectarChofer',
								array("usuario"=>'xsd:string',"contrasenia"=>'xsd:string',"num_movil"=>'xsd:int',"estado"=>'xsd:string'),
								array("return"=>'xsd:boolean'),
								$urlns);
								
	$servidor->register('actualizarEstado',
								array("estado"=>'xsd:string',"usuario"=>'xsd:string'),
								array("return"=>'xsd:boolean'),
								$urlns);
								
	$servidor->register('actualizarUbicacion',
								array("usuario"=>'xsd:string',"ulatitud"=>'xsd:string',"ulongitud"=>'xsd:string',"estado"=>'xsd:string'),
								array("return"=>'xsd:boolean'),
								$urlns);
								
 	$servidor->register('desconectarChofer',
 								array("usuario"=>'xsd:string',"num_movil"=>'xsd:int'),
 								array("return"=>'xsd:boolean'),
 								$urlns);
 								
 	$servidor->register('mensajeSos',
 								array("usuario"=>'xsd:string'),
 								array("return"=>'xsd:boolean'),
 								$urlns);
 								
 	$servidor->register('obtenerMoviles',
 								array("usuario"=>'xsd:string'),
 								array("return"=>'tns:ArregloMoviles'),
 								$urlns);

 	$servidor->register('asignarClaveGCM',
 								array("usuario" => 'xsd:string', "claveGCM" => 'xsd:string'),
 								array("return" => 'xsd:boolean'),
 								$urlns);
 	$servidor->register('notificarEstadoPasajeEnCurso',
 								array("idPasaje" => 'xsd:int', "estado" => 'xsd:string', "usuario" => 'xsd:string'),
 								array("return" => 'xsd:boolean'),
 								$urlns);
								
								
	//Se agregan las estructuras de datos necesarias
	
	$servidor->wsdl->addComplexType(
									'Movil',
									'complexType',
									'struct',
									'all',
									'',
									 array(
                        			'numero'            => array('name' => 'numero', 'type' => 'xsd:int'),
                        			'marca'            => array('name' => 'marca', 'type' => 'xsd:string'),
                        			'modelo'       => array('name' => 'modelo', 'type' => 'xsd:string' ),
                            ));
	$servidor->wsdl->addComplexType('ArregloMoviles',
												'complexType',
												'array',
												'',
												'SOAP-ENC:Array',
												 array(),
											    array(array('ref' => 'SOAP-ENC:arrayType',
											         'wsdl:arrayType' => 'tns:Movil[]')
											        ),
											    'tns:Movil');
								
	//Implementación de las funciones necesarias
	function hola($nombre) {

		return "Hola ".$nombre;

	}
	
	function login($usuario, $contrasenia) {
			$com = establecerConexion();
			if(!$com){
				echo "Error al conectar con la Base de Datos"; 
				exit();
			}
	
			$consultaOk = false;
			
			$consultaEsChoferRegistrado="select id, usuario from Choferes where usuario='$usuario' and contrasenia='$contrasenia' and activo=true and habilitado=true";
			$consultaChoferEstaConectado = "select usuario from ChoferesConectados where usuario='$usuario' and (estado_movil='LIBRE' or estado_movil='OCUPADO')";			
			$totalCampos=mysql_num_rows(mysql_query($consultaEsChoferRegistrado));
			
			if($totalCampos==1){
				
				if(mysql_num_rows(mysql_query($consultaChoferEstaConectado)) == 0){
					$consultaOk = true;
					
				}else {
					$consultaOk=false;
					
				}
			}else {
				$consultaOk = false;
			}
			
			mysql_close($com);
		
			return $consultaOk;
	
	}
	
	
	function conectarChofer($usuario, $contrasenia, $num_movil, $estado) {

			$com = establecerConexion();
			if(!$com){
				echo "Error al conectar con la Base de Datos"; 
				exit();
			}

			$consultaOk = false;
			
			$consultaEsChoferRegistrado="select id, usuario from Choferes where usuario='$usuario' and contrasenia='$contrasenia'";
			$consultaChoferEstaConectado = "select usuario from ChoferesConectados where usuario='$usuario'";			
			$consultaConectarChofer = "insert into ChoferesConectados(usuario,numero_movil,estado_movil) values ('$usuario','$num_movil','$estado')";
			$consultaUpdateEstadoMovil="update ChoferesConectados set estado_movil='LIBRE' where usuario='$usuario'";
			
			$totalCampos=mysql_num_rows(mysql_query($consultaEsChoferRegistrado));
			
			if($totalCampos==1){
				
				if(mysql_num_rows(mysql_query($consultaChoferEstaConectado)) == 0){
					$consultaOk = mysql_query($consultaConectarChofer);
					
				}else {
					$consultaOk=mysql_query($consultaUpdateEstadoMovil);
					
				}
			}else {
				$consultaOk = false;
			}
		
			mysql_close($com);
		
			return $consultaOk;
						
		}	
		
	function actualizarEstado($estado,$usuario) {
		$com = establecerConexion();
			if(!$com){
				echo "Error al conectar con la Base de Datos";
				exit();
			}
		
		$consulta="update ChoferesConectados set estado_movil='$estado' where usuario='$usuario'";
		$consultaOk=mysql_query($consulta);
		mysql_close($com);
		
		return $consultaOk;
		
	}


	function actualizarUbicacion($usuario, $ulatitud, $ulongitud, $estado) {
		
		$com = establecerConexion();
			if(!$com){
				echo "Error al conectar con la Base de Datos";
				exit();
			}
			
		$fechaActual = date("y-m-d H:i:s");
		
		$consulta="update ChoferesConectados set ubicacion_lat=$ulatitud, ubicacion_lon=$ulongitud, ultima_actualizacion='$fechaActual', estado_movil='$estado' where usuario='$usuario'";
		$consultaOk=mysql_query($consulta);
		mysql_close($com);
		
		return $consultaOk;
	}

function desconectarChofer($usuario, $num_movil) {
		$com = establecerConexion();
			if(!$com){
				echo "Error al conectar con la Base de Datos";
				exit();
			}
		$consultaEliminarChoferConectado = "delete from ChoferesConectados where usuario='$usuario'";
		
		$consultaOk=mysql_query($consultaEliminarChoferConectado);
		mysql_close($com);
		
		return $consultaOk;

}

function obtenerMoviles($usuario) {
	$com = establecerConexion();
			if(!$com){
				echo "Error al conectar con la Base de Datos";
				exit();
			}
		
		$consultaMovilesChofer = "SELECT Moviles.numero, Moviles.marca, Moviles.modelo FROM Moviles INNER JOIN AsignacionesMovil ON Moviles.id = AsignacionesMovil.id_movil INNER JOIN Choferes ON AsignacionesMovil.id_chofer = Choferes.id WHERE Choferes.usuario='$usuario' order by Moviles.numero";
		
		$resultado = mysql_query($consultaMovilesChofer);
		$c=0;
		while($registro=mysql_fetch_array($resultado)) {
				$moviles[$c]['numero']=$registro[0];
				$moviles[$c]['marca']=$registro[1];
				$moviles[$c]['modelo']=$registro[2];
				$c++;
			}
		return $moviles;
}

function mensajeSos($usuario) {
	$com = establecerConexion();
			if(!$com){
				echo "Error al conectar con la Base de Datos";
				exit();
			}
		
		$consulta = "update ChoferesConectados set sos=true where usuario='$usuario'";
		$consultaOk=mysql_query($consulta);
		mysql_close($com);
		
		return $consultaOk;
}

function asignarClaveGCM($usuario, $claveGCM){

	$com = establecerConexion();
			if(!$com){
				echo "Error al conectar con la Base de Datos";
				exit();
			}
		
		$consulta = "update Choferes set clave_gcm='$claveGCM' where usuario='$usuario'";
		$consultaOk=mysql_query($consulta);
		mysql_close($com);
		
		return $consultaOk;

}

function notificarEstadoPasajeEnCurso($idPasaje, $estado, $usuario){

	$com = establecerConexion();
			if(!$com){
				echo "Error al conectar con la Base de Datos";
				exit();
			}
		
		$consulta = "update PasajesEnCurso set estado='$estado' where id=$idPasaje";
		$consultaOk=mysql_query($consulta);
		if($consultaOk) {
			if($estado=="rechazado"){
				$consulta2 = "update ChoferesConectados set estado_movil='LIBRE' where usuario='$usuario'";
				$consultaOk=mysql_query($consulta2);
			}
		}
		mysql_close($com);
		
		return $consultaOk;

}
	
	$servidor->service($HTTP_RAW_POST_DATA);
	

?>
