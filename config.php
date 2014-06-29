<?php

// EN ESTE ARCHIVO DEFINIREMOS TODOS LOS DATOS DE CONFIGURACIÓN

// TÍTULO Y URL DE LA WEB 
define("TITULO_WEB","Backoffice Talent");

// CONFIGURACION BASE DE DATOS
define("BD_SERVIDOR","localhost");	// servidor bd
define("BD_USUARIO","root");		// login bd
define("BD_PASS","");			    // pass bd
define("BD_NOMBRE","montepio");		// nombre bd

// EMAIL
define("EMAIL_DIRECCION", "no-reply@montepioportuario.org");
define("EMAIL_USUARIO", "no-reply@montepioportuario.org");
define("EMAIL_PASS", "");
define("EMAIL_SMTP", "");
define("EMAIL_PUERTO", "25");
define("EMAIL_SSL", false);

// ARCHIVOS
define("IMAGENES_PERMITIDAS","jpg jpeg gif png bmp");
define("DOCUMENTOS_PERMITIDOS","ppt pptx doc docx odf pdf txt xls xlsx zip rar");
define("TAM_MAX","10485760"); // 10MB
define("TAM_MINIATURAS_ALTO", "220");
define("TAM_MINIATURAS_ANCHO", "165");

// OPCIONES DE CACHÉ INTELIGENTE Y SESIÓN
define("CACHE_ACTIVADO", false);
define("TTL_CACHE",86400); // tiempo de duración del caché. Por defecto 1 día.
define("TTL_SESION",1200); //tiempo en segundos que una sesión de usuario puede estar inactiva antes de cerrarla automáticamente, 20 min
define("LIMPIAR_HTML",false); // limpiará los campos de tipo html para que cumplan el formato xhtml
define("SELECT_LIMIT",50); // numero por defecto de registros mostrados por página
define("FORZAR_SSL", false);

// FORMATOS DE FECHA
define("FORMATO_FECHA","d/m/Y");
define("FORMATO_FECHA_MYSQL","%d/%m/%Y");
define("FORMATO_FECHA_MYSQL_ANYO","%Y");
define("FECHA_VACIA","00/00/0000");

// CONFIGURACIÓN INTERNA
define("BACKOFFICE_CLAVE","clave_secreta_backoffice2012"); // texto para crear hashes md5


//CONFIG RUTAS INTERNAS.

$path = substr( __FILE__, strlen( $_SERVER[ 'DOCUMENT_ROOT' ] ) );
$url  = ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'];
$search  = array( DIRECTORY_SEPARATOR.basename(__FILE__), '\\', '//' );
$replace = array('', '/', '/');
$path = str_replace($search, $replace, $path);
$url.=  (0 === strpos($path, '/')) ? $path."/" : "/".$path."/";

define("CARPETA_ARCHIVOS","archivos/"); // carpeta donde se alojarán los archivos (relativa a este archivo de configuración)
define("URL_BACKOFFICE", $url );
define("RUTA_ARCHIVOS",realpath(dirname(__FILE__))."/".CARPETA_ARCHIVOS); // ruta absoluta donde almacenaremos los archivos
define("RUTA_MINIATURAS",RUTA_ARCHIVOS."miniaturas/");
define("RUTA_DOCUMENTOS",RUTA_ARCHIVOS."documentos/");
define("RUTA_CACHE", RUTA_ARCHIVOS."cache/");
define("URL_ARCHIVOS", URL_BACKOFFICE."/".CARPETA_ARCHIVOS);
define("URL_MINIATURAS", URL_BACKOFFICE.CARPETA_ARCHIVOS."miniaturas/");
define("URL_DOCUMENTOS", URL_BACKOFFICE.CARPETA_ARCHIVOS."documentos/");


?>