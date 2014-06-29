<?php
session_start();
require 'clases/inc.php';

// si tenemos activada la opción FORZAR_SSL redirigiremos la pag a la dirección https
if(isset($_SERVER['HTTPS'])){
	if ($_SERVER['HTTPS'] != "on" && FORZAR_SSL) { 
		$url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']; 
		header("Location: $url"); 
		exit();
	}
}

// escuchamos peticiones de datos
if(isset($_REQUEST["action"])){
	Restful::Run();
	exit(0);
}

?>
<!DOCTYPE>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="imagenes/favicon.ico" rel="icon" type="image/x-icon" />
		<meta charset="UTF-8">
		<title><?php echo TITULO_WEB; ?></title>
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css" rel="stylesheet">
		<script>
			var CONFIG = {};
			CONFIG.administar_noticias   = <?php if( Usuario::TienePermiso('administrar_noticias') ) echo "true"; else echo "false"; ?>;
			CONFIG.administar_documentos = <?php if( Usuario::TienePermiso('administrar_documentos') ) echo "true"; else echo "false"; ?>;
			CONFIG.administar_roles      = <?php if( Usuario::TienePermiso('administrar_roles') ) echo "true"; else echo "false"; ?>;
			CONFIG.administar_usuarios   = <?php if( Usuario::TienePermiso('administrar_usuarios') ) echo "true"; else echo "false"; ?>;
			CONFIG.administar_categorias = <?php if( Usuario::TienePermiso('administrar_categorias') ) echo "true"; else echo "false"; ?>;

			CONFIG.dateFormat         = <?php echo "'".FORMATO_FECHA."'" ?>;
			CONFIG.UrlRelArchivos     = <?php echo "'".URL_ARCHIVOS."'" ?>;
			CONFIG.ResultsPerPage     = <?php echo "'".SELECT_LIMIT."'" ?>;
			CONFIG.UrlDocumentos      = <?php echo "'".URL_DOCUMENTOS."'" ?>;	
		</script>	
		<script id="microloader" type="text/javascript" src="bootstrap.js"></script>
	</head>
	<body>
		<div id="loading-mask" style=""></div>
			<div id="loading">
			<div class="loading-indicator">
				<img alt="Cargando" src="resources/custom/ajax-loader.gif" width="48" height="48" style="margin-right:8px;float:left;vertical-align:top;"/><?php echo TITULO_WEB; ?>
				<br /><span id="loading-msg">Cargando...</span>
			</div>
		</div>
	</body>
</html>
