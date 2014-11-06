<?php
//incluimos la clase nusoap.php
require_once('lib/nusoap.php');

//creamos el objeto de tipo soapclient.
//http://www.mydomain.com/server.php se refiere a la url
//donde se encuentra el servicio SOAP que vamos a utilizar.
$soapclient = new nusoap_client( 'http://www.silav.hol.es/Web Service/servicio.php');


//Llamamos la función que habíamos implementado en el Web Service
//e imprimimos lo que nos devuelve
echo $soapclient->call('esChoferRegistrado',array( 'usuario'=>'jerech','password'=>'12345jc'));

echo '<pre>' . htmlspecialchars($soapclient->request, ENT_QUOTES) . '</pre>';
echo '<h2>Response</h2>';
echo '<pre>' . htmlspecialchars($soapclient->response, ENT_QUOTES) . '</pre>';

?>