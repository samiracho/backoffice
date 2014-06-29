<?php
	// Clase ObjetoBD.
	// Implementa las funciones básicas para listar, insertar, actualizar y eliminar. Así como la validación de los datos obtenidos del cliente
	
	class ObjetoBD
	{	
		protected $validacion="";			//array con los tipos de campos y sus reglas de validación
		protected $consultaInsertar="";
		protected $consultaActualizar="";
		protected $consultaListar="";
		protected $exitoInsertar="";
		protected $exitoActualizar="";
		protected $exitoListar="";
		protected $errorListar="";
		protected $errorInsertar="";
		protected $errorActualizar="";	
		protected $campos="";
		protected $busqueda = "";
		protected $relaciones="";
		protected $tabla="";
		protected $id="";
		protected $tablasCache="";
		
		// $consulta: Consulta para obtener los datos
		// $cache: si es true intentará obtener la respuesta de la caché
		protected function Listar($consulta,$cache=CACHE_ACTIVADO, $filtros = null, $start = null, $limit = null, $sort = null )
		{
			$filtroBusqueda = !empty($filtros) ? $this->CrearFiltro($filtros): "";
			
			$orderby = (stripos($consulta,"ORDER BY")) ? "" :  " ORDER BY 1 DESC";
			$sortBusqueda   = !empty($sort) ? $this->CrearSort($sort) :	$orderby;
			
			$limite  = (  strval(intval($start)) == strval($start) ) && (  strval(intval($limit)) == strval($limit) ) ? " LIMIT ".$start.",".$limit : "";
			
			// le aplicamos lo filtros, los criterios de ordenación y los límites a las consultas
			$consulta .= " ".$filtroBusqueda." ".$sortBusqueda." ".$limite;
			
			//print_r($consulta);
			
			$resultado = "";
			if($cache && $filtros == null && $sort == null){
				
				$resultado = Cache::Obtener($this->tabla,$consulta);
				
				if(!$resultado) {
					$resultado = $this->Lista($consulta);
					Cache::Guardar($this->tabla,$consulta,$resultado);
					return $resultado;
				} else return $resultado;
			}
			else return $this->Lista($consulta);
		}
		
		protected function Lista($consulta)
		{
			$bd 		= BD::Instancia();
			$listado	= array();
			$temp       = array();
			$res		= new Comunicacion();
			
			$consulta = preg_replace("/SELECT/", "SELECT SQL_CALC_FOUND_ROWS", $consulta, 1);
			
			$datos = $bd->Ejecutar($consulta);
			
			if( $bd->ObtenerErrores() == '' ) {		
				$rows = $bd->ObtenerResultados("SELECT FOUND_ROWS() AS numfilas;");
				$total= $rows[0]['numfilas'];
				
				$listado = array();
				
				// obtenemos una lista con todos los registros
				while($fila = $bd->ObtenerFila($datos)) {
					foreach ($fila as $campo => $valor) {
						
						$temp[$campo] = stripcslashes($valor);
						
						// para ignorar las fechas vacías
						if ($valor == "0000-00-00" || $valor =="00/00/0000")$temp[$campo] = "";
					}
					$listado[] = $temp;
				}
				
				// construimos la resupesta JSON
				$res->exito   = true;
				$res->mensaje = $this->exitoListar;
				$res->errores = "";
				$res->datos   = $listado;
				$res->total   = $total;
			} else {
				$res->exito = false;
				$res->mensaje = $this->errorListar;
				$res->errores = $bd->ObtenerErrores();
			}
			
			return $res;
		}
		
		
		// construye un objeto json con los campos de búsqueda declarados en un objeto
		protected function ObtenerJsonBusqueda()
		{		
			$bd      = BD::Instancia();
			$temp    = array();
			$temp2   = array();
			
			foreach ($this->busqueda as $campo => $valor) {
				unset($valor['consulta']);
				
				if( !isset ($valor['nobuscador']) ) {
					foreach ($valor['campos'] as $filtro => $opciones) {
						if($opciones['opciones'] == null ) {
							$opciones['opciones'] = "";
						}
						
						// si es una consulta sql la ejecutamos y obtenemos un array con los resultados
						else if(!is_array($opciones['opciones']))
						{
							$datos = $bd->Ejecutar($opciones['opciones']);
							$valores = array();		
							while($fila = $bd->ObtenerFila($datos)) {
								foreach ($fila as $key => $value) {
									$valores[] = $value;
								}
							}
							$opciones['opciones'] = $valores;							
						}
						
						$temp2[] = array('nombre' => $filtro, 'tipo'=>$opciones['tipo'],'opciones' => $opciones['opciones']);	
					}
					
					$temp[] = array('nombre' =>$campo, 'campos'=>$temp2);
					$temp2   = array();
				}
			}
			print_r( "var camposBusqueda =".json_encode($temp) );
		}
		
		protected function ConstruirPaginador($consulta, $pagina = 1,$resPorPagina = 1, $url = '?')
		{        
			$bd = BD::Instancia();
			$total = $bd->ContarFilas($consulta);	
			$adyacentes = 1; 

			$pagina = ($pagina == 0 ? 1 : $pagina);  
			$start = ($pagina - 1) * $resPorPagina;								
			
			$prev = $pagina - 1;							
			$next = $pagina + 1;
			$ultimaPagina = ceil($total/$resPorPagina);
			$lpm1 = $ultimaPagina - 1;
			
			$paginacion = "";
			if($ultimaPagina > 1)
			{	
				$paginacion .= "<ul class='pagination'>";
				$paginacion .= "<li class='details'>pagina $pagina de $ultimaPagina</li>";
				if ($ultimaPagina < 7 + ($adyacentes * 2)) {	
					for ($counter = 1; $counter <= $ultimaPagina; $counter++){
						if ($counter == $pagina)
							$paginacion.= "<li><a class='current'>$counter</a></li>";
						else
							$paginacion.= "<li><a href='".$url."pagina=".$counter."'>".$counter."</a></li>";					
					}
				}
				elseif($ultimaPagina > 5 + ($adyacentes * 2)) {
					if($pagina < 1 + ($adyacentes * 2))	{
						for ($counter = 1; $counter < 4 + ($adyacentes * 2); $counter++) {
							if ($counter == $pagina)
								$paginacion.= "<li><a class='current'>$counter</a></li>";
							else
								$paginacion.= "<li><a href='{$url}pagina=$counter'>$counter</a></li>";					
						}
						$paginacion.= "<li class='dot'>...</li>";
						$paginacion.= "<li><a href='{$url}pagina=$lpm1'>$lpm1</a></li>";
						$paginacion.= "<li><a href='{$url}pagina=$ultimaPagina'>$ultimaPagina</a></li>";		
					}
					elseif($ultimaPagina - ($adyacentes * 2) > $pagina && $pagina > ($adyacentes * 2))
					{
						$paginacion.= "<li><a href='{$url}pagina=1'>1</a></li>";
						$paginacion.= "<li><a href='{$url}pagina=2'>2</a></li>";
						$paginacion.= "<li class='dot'>...</li>";
						for ($counter = $pagina - $adyacentes; $counter <= $pagina + $adyacentes; $counter++)
						{
							if ($counter == $pagina)
								$paginacion.= "<li><a class='current'>$counter</a></li>";
							else
								$paginacion.= "<li><a href='{$url}pagina=$counter'>$counter</a></li>";					
						}
						$paginacion.= "<li class='dot'>..</li>";
						$paginacion.= "<li><a href='{$url}pagina=$lpm1'>$lpm1</a></li>";
						$paginacion.= "<li><a href='{$url}pagina=$ultimaPagina'>$ultimaPagina</a></li>";		
					} else {
						$paginacion.= "<li><a href='{$url}pagina=1'>1</a></li>";
						$paginacion.= "<li><a href='{$url}pagina=2'>2</a></li>";
						$paginacion.= "<li class='dot'>..</li>";
						for ($counter = $ultimaPagina - (2 + ($adyacentes * 2)); $counter <= $ultimaPagina; $counter++) {
							if ($counter == $pagina)
								$paginacion.= "<li><a class='current'>$counter</a></li>";
							else
								$paginacion.= "<li><a href='{$url}pagina=$counter'>$counter</a></li>";					
						}
					}
				}
				
				if ($pagina < $counter - 1) { 
					$paginacion.= "<li><a href='{$url}pagina=$next'>Siguiente</a></li>";
					$paginacion.= "<li><a href='{$url}pagina=$ultimaPagina'>Última</a></li>";
				} else {
					$paginacion.= "<li><a class='current'>Siguiente</a></li>";
					$paginacion.= "<li><a class='current'>Última</a></li>";
				}
				$paginacion.= "</ul>\n";		
			}
		
			return $paginacion;
		} 
		
		private static function CrearMiniatura($nombreArchivo, $ruta = RUTA_ARCHIVOS, $rutaMiniaturas = RUTA_MINIATURAS)
		{
			require_once 'thumbnailer/ThumbLib.inc.php';
			
			$extension = pathinfo($ruta.$nombreArchivo, PATHINFO_EXTENSION);
			
			if($extension == 'gif' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'bmp') {
				
				// crear la miniatura
				$thumb = PhpThumbFactory::create($ruta."/".$nombreArchivo);
				$thumb->adaptiveResize(TAM_MINIATURAS_ANCHO, TAM_MINIATURAS_ALTO);
				$thumb->save($rutaMiniaturas.$nombreArchivo);
				return $nombreArchivo;
			}
			else return "";
		}
		
		protected static function ObtenerMiniatura($nombreArchivo, $ruta = RUTA_ARCHIVOS, $rutaMiniaturas = RUTA_MINIATURAS)
		{
			$extension = pathinfo($ruta.$nombreArchivo, PATHINFO_EXTENSION);
			$thumb = $extension.".png";
			$miniatura = "";
			
			if ( file_exists($rutaMiniaturas.$nombreArchivo) ) $miniatura = $nombreArchivo;
			else if ( file_exists($rutaMiniaturas.'extensiones/'.$thumb) ) $miniatura = '/extensiones/'.$thumb;
			else $miniatura = '/extensiones/desconocido.png';
			
			$result = $miniatura != "" ? URL_MINIATURAS.$miniatura : "";
			return $result;
		}
	
		protected static function EliminarArchivo($nombreArchivo = "", $ruta = RUTA_ARCHIVOS)
		{
			if ($nombreArchivo == "") return false;
			
			$rutaArchivoAntiguo = $ruta.$nombreArchivo;
			$rutaMiniaturaAntigua = RUTA_MINIATURAS.$nombreArchivo;
			
			if(!is_writeable($rutaArchivoAntiguo) || !is_file($rutaArchivoAntiguo)) {		
				return false;		
			}
			else 
			{
				// destruimos el archivo antiguo
				unlink($rutaArchivoAntiguo);
				
				if(is_writeable($rutaMiniaturaAntigua) && is_file($rutaMiniaturaAntigua)) {		
					// destruimos la miniatura antigua si la hubiera
					unlink($rutaMiniaturaAntigua);		
				}
				
				return true;
			}
		}
		
		// convierte un archivo temporal en uno vitalicio
		// devuelve un string con el nombre del archivo final
		protected static function AlmacenarArchivo($nombreArchivo = "",$crearMiniatura = true, $ruta = RUTA_ARCHIVOS)
		{	
			$rutaArchivoAntiguo = $ruta.$nombreArchivo;		
			if(is_writeable($rutaArchivoAntiguo) && is_file($rutaArchivoAntiguo)) {		
				$nombreArchivo =  preg_replace('/temp_/','',$nombreArchivo,1);
				rename($rutaArchivoAntiguo, $ruta.$nombreArchivo );
				
				// creamos la miniatura
				if($crearMiniatura) ObjetoBD::CrearMiniatura($nombreArchivo, $ruta);
			}
			return $nombreArchivo;
		}
		
		protected static function EliminarTemporales($ruta = RUTA_ARCHIVOS)
		{
			$expire_time = TTL_SESION*2;
			foreach (glob($ruta."temp_*") as $Filename) {
 
				// Read file creation time
				$FileCreationTime = filectime($Filename);
			 
				// Calculate file age in seconds
				$FileAge = time() - $FileCreationTime; 
			 
				// Is the file older than the given time span?
				if ($FileAge > ($expire_time)){
					unlink($Filename);
				}
			}	
		}
		
		protected static function SubirArchivo($nombreArchivo = "", $eliminarTemporales = true, $temporal = true, $extensiones = IMAGENES_PERMITIDAS, $tamMax = TAM_MAX, $ruta = RUTA_ARCHIVOS, $urlRelativa = null )
		{
			global $_FILES;
			
			$bd            = BD::Instancia();
			$res           = new Comunicacion();
			$directorio    = "";
			$tipo          = "";
			
			// si hay un archivo antiguo lo eliminamos
			ObjetoBD::EliminarArchivo($nombreArchivo, $ruta);
			
			// eliminamos los temporales
			if($eliminarTemporales)ObjetoBD::EliminarTemporales($ruta);
			
			// quitamos caracteres extraños
			$nombreArchivo = preg_replace('/[^(\x20-\x7F)]*/','', $_FILES['file_path']['name']);
			
			// extensión del archivo	
			$ext = pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION);
			
			if( !in_array($ext,explode(' ', $extensiones)) ) {
				$res->exito   = false;
				$res->mensaje = t('Error');
				$res->errores = t('The file you attempted to upload is not allowed.');
				return $res;
			}

			$rutaAbsoluta  = $ruta;
			
			// creamos un nombre único para el archivo
			do {
				$prefijo = substr(md5(uniqid(rand())),0,6);
				if($temporal) $prefijo = 'temp_'.$prefijo;
				$nombreArchivo = $prefijo.'_'.$nombreArchivo;
			} 
			while ( file_exists($rutaAbsoluta.$nombreArchivo) );
			

			if(filesize($_FILES['file_path']['tmp_name']) > $tamMax) {
				$res->exito   = false;
				$res->mensaje = t('Error');
				$res->errores = t('The file you attempted to upload is too large.');
				return $res;
			}
			
			// Check if we can upload to the specified path, if not DIE and inform the user.
			if(!is_writable($rutaAbsoluta)) {
				$res->exito   = false;
				$res->mensaje = t('Error');
				$res->errores = t('You cannot upload to the specified directory, please CHMOD it to 777.');
				return $res;
			}

			if(move_uploaded_file($_FILES['file_path']['tmp_name'],$rutaAbsoluta.$nombreArchivo))
			{				
				$res->exito   = true;
				$res->mensaje = t("File uploaded");
				$res->errores = "";
				$res->datos = array('ruta' => $rutaAbsoluta, 'nombre' => $nombreArchivo, 'urlrel' =>$urlRelativa);
			}
			else
			{
				$res->exito   = false;
				$res->mensaje = t('Error');
				$res->errores = t('Error.');
			}
			return $res;
		}
	
		protected function ObtenerArray($consulta)
		{
			$bd 		= BD::Instancia();
			$listado	= array();
			$temp       = array();
			
			$datos = $bd->Ejecutar($consulta);
			
			if( $bd->ObtenerErrores() == '' ) {			
				// obtenemos una lista con todos los registros
				while($fila = $bd->ObtenerFila($datos)) {
					foreach ($fila as $campo => $valor) {
						// para ignorar las fechas vacías
						if ($valor == "0000-00-00" || $valor =="00/00/0000")$valor="";
						$temp[$campo] = $valor;
					}
					$listado[] = $temp;
				}
			}
			
			return $listado;
		}
	
		// comprueba que el valor sea único
		protected static function ComprobarUnico($idComprobar, $valorComprobar, $tabla,$id = '',$valorId='')
		{
			$bd 		= BD::Instancia();
			$res		= new Comunicacion();
			$consulta 	= "";
			
			$id             = mysql_real_escape_string($id);
			$valorId        = mysql_real_escape_string($valorId);
			$idComprobar    = mysql_real_escape_string($idComprobar);
			$tabla          = mysql_real_escape_string($tabla);
			$valorComprobar = mysql_real_escape_string($valorComprobar);
		
			if($valorId =='') {
				$consulta = "SELECT COUNT(*) FROM ".$tabla." WHERE ".$idComprobar." = '". $valorComprobar ."' ";			
			}
			else {
				$consulta = "SELECT COUNT(*) FROM ".$tabla." WHERE ".$id."!='".$valorId."' AND ".$idComprobar."= '". $valorComprobar ."' ";
			}
			
			if ( $bd->ContarFilas($consulta) > 0 ) {
				$res->exito = false;
				$res->mensaje = t("Error, already exists");
				$res->errores = array( array('id'=>$idComprobar,'msg'=> $idComprobar." ".t('Already exists')));
			} else {
				$res->exito = true;
				$res->mensaje = t("OK, is unique");
				$res->errores = t("");
			
			}
			return $res;
		}
		
		// crea un objeto en base a un mensaje Json
		protected function Leer()
		{
			$res = new Comunicacion();			
			$res->LeerJson();
			
			if(is_array($res->datos) && sizeof($res->datos) > 0 )$datos = $res->datos[sizeof($res->datos)-1];
			else $datos = $res->datos;
			
			foreach ($datos as $campo => $valor) {
				if (array_key_exists($campo, $this->campos)) {						
					$this->campos[$campo]['valor'] = $valor;
				}
					
				if ($this->relaciones != ""){
					foreach ($this->relaciones as $rel => $val) {
						if (array_key_exists($campo, $this->relaciones[$rel]['campos'])) {						
							$this->relaciones[$rel]['campos'][$campo]['valor'] = $valor;
						}
					}
				}
			}
		}
		
		// crea un order by mysql a partir de un array json
		protected function CrearSort($sort)
		{
			$sortSQL = "";
			$d="";
			$camposValidos = $this->campos;
			
			if($this->relaciones!=""){
				foreach($this->relaciones as $rel=>$val){
					$camposValidos = array_merge($camposValidos,$this->relaciones[$rel]['campos']);
				}
			}
			
			if(!empty($sort)){	
				$filtros = Comunicacion::DecodificarJson($sort, true);
				
				foreach($filtros as $campo => $valor){
					if(!empty($valor->property) && !empty($valor->direction)){
						if (array_key_exists($valor->property, $camposValidos)) {
							if($valor->direction == 'ASC' || $valor->direction == 'DESC'){
								$propiedad = $valor->property;
								
								// esto lo hago para que si se ordena por fecha lo haga utilizando el formato interno de fechas de mysql y no el que se le muestra al cliente
								if ($camposValidos[$valor->property]['tipo'] == 'date') $propiedad = "sort".$propiedad;
								
								if($sortSQL == "") $sortSQL = "ORDER BY ".$propiedad." ".$valor->direction;
								else  $sortSQL.= ", ".$propiedad." ".$valor->direction;
							}
						}
					}
				}
			}
			
			return $sortSQL;
		}
		
		// crea un filtro mysql a partir de un array json filtros{'nombre': nombre_columna, 'valor': valor }
		// si la clave o el valor son inválidos entonces devuelve una cadena vacía
		protected function CrearFiltro($listaFiltros)
		{
			$filtroSQL = '';
			$operador = '';
			$numoperador = '';
			$comparador = '';
			$d="";
			$camposValidos = $this->campos;
			$valor = "";
			
			if($this->relaciones!=""){
				foreach($this->relaciones as $rel=>$val){
					$camposValidos = array_merge($camposValidos,$this->relaciones[$rel]['campos']);
				}
			}
			
			if(!empty($listaFiltros)){
				$filtros = Comunicacion::DecodificarJson($listaFiltros,true);
			
				foreach($filtros->filtros as $campo => $valor){
					if(!empty($valor->nombre) && !empty($valor->valor)){
						if (array_key_exists($valor->nombre, $camposValidos)){
							// solo aceptaremos este tipo de operadores para evitar inyecciones SQL. Si no es ninguno de ellos presuponemos que será un AND
							$numoperador    = !empty($valor->operador) ? intval($valor->operador) : 1;
							if($numoperador === 1)$operador = 'AND';
							if($numoperador === 2)$operador = 'AND NOT';
							if($numoperador === 3)$operador = 'OR';
							if($numoperador === 4)$operador = 'OR NOT';
							if($numoperador === 5)$operador = ' (';
							if($numoperador === 6)$operador = ' )';
							
							// solo aceptaremos este tipo de comparadores para evitar inyecciones SQL. Si no es ninguno de ellos presuponemos que será un LIKE
							$numcomparador    = !empty($valor->comparador) ? intval($valor->comparador) : 3;
							if($numcomparador === 1)$comparador = '=';
							if($numcomparador === 2)$comparador = '!=';
							if($numcomparador === 3)$comparador = 'LIKE';
							if($numcomparador === 4)$comparador = 'NOT LIKE';
							if($numcomparador === 5)$comparador = '>';
							if($numcomparador === 6)$comparador = '<';
							
							if(isset($this->campos[$valor->nombre]) ){					
								$busq = ($this->campos[$valor->nombre]['tipo'] == 'int') ? "'".intval($valor->valor)."'" : "'".mysql_real_escape_string($valor->valor)."'";					
								$filtroSQL .= " ".$operador." ".$valor->nombre;
								$filtroSQL .= ($numcomparador === 3 || $numcomparador === 4) ? " ".$comparador." '%".mysql_real_escape_string($valor->valor)."%' " : $comparador.$busq;	
							} else {
								$filtroSQL .= " ".$operador." ".$valor->nombre." LIKE '%".mysql_real_escape_string($valor->valor)."%' ";
							}		
						}
					}
				}
			}
			return $filtroSQL;
		}
		
		// limpia los datos para evitar inyecciones sql y comprueba si son válidos
		// parámetro opcional. Array de campos a comprobar
		protected function ValidarCampos(&$camposComprobar=null)
		{			
			// array donde guardaremos los errores
			$errores	= array();
			$correcto   = true;

			if($camposComprobar == null)$camposComprobar = &$this->campos;
			
			foreach( $camposComprobar as $campo => $valor )
			{		
				$correcto = true;
				
				// si no se ha especificado usuario será el usuario actual
				if($valor['tipo'] == 'user_id' ){	
					 $camposComprobar[$campo]['valor'] = intval(Usuario::IdUsuario()); 		 
				}		
				
				if(!$valor['nulo'] && $valor['valor'] == ""  ){
					$correcto = false;
				}
				
				if($correcto && $valor['valor']!="" && !$valor['nulo'] ){
					switch($valor['tipo']){
					
						case 'int':
						case 'user_id':
							if( strval(intval($valor['valor'])) != strval($valor['valor']) ) $correcto = false;
						break;
						
						case 'id':
							if( intval($valor['valor']) < 1 ) $correcto = false;
						break;
						
						case 'string':
						case 'file':
						case 'html':
						case 'date':
							if(!is_string($valor['valor'])) $correcto = false;
						break;
						
						case 'checkbox':
						break;
						
						case 'commasint':
							if(!is_string($valor['valor'])) $correcto = false;
							else{
								$intarray = explode(',',$valor['valor']);
								foreach ($intarray as $item)
								{
									if(  strval(intval($item)) != strval($item) ){
										$correcto = false;
										break;
									}
								}	
							}
						break;
						
						case 'email':
							if (filter_var($valor['valor'],FILTER_VALIDATE_EMAIL) ) $correcto = false; 
						break;
						
						default:
							$correcto = false;
						break;
					}
				}
			
				// si el campo no es válido lo agregamos al array de errores
				if(!$correcto){
					$errores[] = array('id'=>$campo,'msg'=> $valor['msg']);
				} else {
					// si es html lo limpiamos
					if( $valor['tipo'] == 'html'){
						if($valor['valor'] == "u200b" )$camposComprobar[$campo]['valor'] = "";
						
						if(LIMPIAR_HTML)
						{
							require_once '../lib/htmlpurifier/HTMLPurifier.auto.php';
							$config = HTMLPurifier_Config::createDefault();
							$config->set('Core.Encoding', 'UTF-8');
							$config->set('HTML.Doctype', FORMATO_HTML);
							$purifier = new HTMLPurifier($config);

							// limpiamos el html
							$camposComprobar[$campo]['valor'] = $purifier->purify($camposComprobar[$campo]['valor']);
						}
					}
					
					// si es de tipo fecha la convertimos en el formato que necesita mysql
					if( $valor['tipo'] == 'date')
					{
						$camposComprobar[$campo]['valor'] = mysqlDate($camposComprobar[$campo]['valor']);
					}
					
					if( $valor['tipo'] == 'commasint' || $valor['tipo'] == 'string')
					{
						$camposComprobar[$campo]['valor'] = trim($camposComprobar[$campo]['valor']);
					}
					
					// si es de tipo checkbox
					if( $valor['tipo'] == 'checkbox')
					{
						if($valor['valor'] == "1" || $valor['valor'] === "on")
						{
							$camposComprobar[$campo]['valor'] = "1";
						}
						else $camposComprobar[$campo]['valor'] = "0";
					}
					
					// limpiamos los posibles caracteres extraños
					$camposComprobar[$campo]['valor'] = mysql_real_escape_string($camposComprobar[$campo]['valor']);
				}				
			}	
			
			return $errores;			
		}
		
		protected function EliminarDato($leerDatos = true)
		{
			$bd = BD::Instancia();
			$consulta = "";
			$res = new Comunicacion();
			
			
			if($leerDatos)$this->Leer();				
			$id = $this->campos[$this->id]['valor'];

			if($id=="")
			{
				$res->exito = false;
				$res->mensaje = t("Error");
				$res->errores = t("Delete operation failed");
				return $res;
			}
			
			// eliminamos los archivos
			foreach( $this->campos as $campo => $valor )
			{
				if($valor['tipo'] == 'file'){ObjetoBD::EliminarArchivo($valor['valor'], $valor['ruta']);}
			}
			
			// eliminamos los datos de la bd
			$consulta = "DELETE FROM ".$this->tabla." WHERE ".$this->id."= '". intval($id) ."'";
			$bd->Ejecutar($consulta);
			
			if( $bd->ObtenerErrores() == "" )
			{
				// eliminamos los archivos de cache de consultas si existieran
				if(CACHE_ACTIVADO){
					Cache::EliminarPorPrefijo($this->tabla);
					if(is_array($this->tablasCache))
					foreach($this->tablasCache as $t){
						Cache::EliminarPorPrefijo($t);
					}
				 }
				
				$res->exito = true;
				$res->mensaje = t("Success");
				$res->errores = "";
				
				if(  is_array($this->relaciones) || $this->relaciones!="" )
				{
					foreach ($this->relaciones as $relacion => $valor)
					{
						if(!$valor['soloLectura'])
						{
							$claveBorrado = isset($valor['claveBorrado']) ? $valor['claveBorrado'] : $valor['claveAjena1'];
							
							$consulta = "DELETE FROM ".$valor['tabla']." WHERE  ".$claveBorrado."= '". intval($id) ."'";
							$bd->Ejecutar($consulta);
					
							// eliminamos los archivos de cache de consultas si existieran
							if(CACHE_ACTIVADO)Cache::EliminarPorPrefijo($valor['tabla']);
					
							if( $bd->ObtenerErrores() != "" )
							{
								$res->exito = false;
								$res->mensaje = t("Error");
								$res->errores = t("Delete relations operation failed");
								return $res;
							}
						}
					}
				}
			}
				
			return $res;
		}

		// $autoConsultas: si es true construirá las consultas INSERT y UPDATE automáticamente, sinó las leerá de los parámetros $this->consultaInsertar y $this->consultaActualizar
		// $leerDatos: si es true, llamará a la función para leer los datos enviados por el cliente.
		protected function Guardar($autoConsultas=true,$leerDatos=true)
		{	
			$bd 		   = BD::Instancia();
			$consulta 	   = "";
			$res		   = new Comunicacion();
			$insertado	   = false;
			$campos        = "";
			$valores       = "";
			$camposValores = "";
			$temp          = array();
			
			if($leerDatos)$this->Leer();
			
			// limpiamos los datos recibidos del formulario (comillas, caracteres extraños e.t.c) 
			$errores = $this->ValidarCampos();

			// si algún campo tiene un valor inválido no continuamos
			if(sizeof($errores) > 0)
			{
				$res->exito = false;
				$res->mensaje = t("Error saving changes");
				$res->errores = $errores;
				return $res;
			}

			// si su id no está definida entonces es un registro nuevo
			if($this->campos[$this->id]['valor']== '' || $this->campos[$this->id]['valor']< 1)
			{	
				// si autoConsultas = true entonces construimos el INSERT automáticamente
				if($autoConsultas)
				{		
					foreach($this->campos as $campo => $valor )
					{
						if(!$valor['lectura'])
						{
							$texto = $valor['valor'];

							// al guardar convertimos el archivo temporal en vitalicio							
							if($valor['tipo']=='file') $texto = ObjetoBD::AlmacenarArchivo($valor['valor'],true, $valor['ruta']);
							
							$campos        .= $campo.",";
							$valores       .= "'".$texto."',";
						}
					}
			
					// les quitamos la última coma
					$campos = substr($campos, 0, -1);
					$valores = substr($valores, 0, -1);
				
					// construimos la consulta
					$consulta = "INSERT INTO ".$this->tabla." ( ".$campos." ) VALUES ( ".$valores." )";
				}
				else $consulta = $this->consultaInsertar;
				
				$insertado = true;
			}
			else // actualizar
			{
				// si autoConsultas = true entonces construimos el UPDATE automáticamente
				if($autoConsultas)
				{
					foreach($this->campos as $campo => $valor )
					{
						if(!$valor['lectura'])
						{
							$texto = $valor['valor'];

							// al guardar convertimos el archivo temporal en vitalicio							
							if($valor['tipo']=='file') $texto = ObjetoBD::AlmacenarArchivo($valor['valor'],true, $valor['ruta']);
							
							$camposValores .= $campo."='".$texto."',";
						}
					}
				
					// le quitamos la última coma
					$camposValores = substr($camposValores, 0, -1);
				
					// construimos la consulta
					$consulta = "UPDATE ".$this->tabla." SET ".$camposValores." WHERE ".$this->id."='".intval($this->campos[$this->id]['valor'])."' ";
				
				}
				else $consulta = $this->consultaActualizar;
				$insertado = false;
			}
			
			$bd->Ejecutar($consulta);
			
			// si todo ha ido bien construimos la respuesta y la devolvemos
			if( $bd->ObtenerErrores() == "" )
			{			
				// eliminamos los archivos de cache de consultas si existieran
				if(CACHE_ACTIVADO){
					Cache::EliminarPorPrefijo($this->tabla);
					if(is_array($this->tablasCache))
					foreach($this->tablasCache as $t){
						Cache::EliminarPorPrefijo($t);
					}
				 }
				
				$res->exito   = true;
				$res->errores = "";
				
				if($insertado)
				{
					$this->campos[$this->id]['valor'] = $bd->ObtenerUltimoID();
					$res->mensaje = $this->exitoInsertar;
				}
				else
				{
					$res->mensaje = $this->exitoActualizar;
				}
				
				foreach($this->campos as $campo => $valor )
				{
						if($valor['tipo'] == 'date'){
							
							// compatibilidad php 5.1
							//$d = date_parse($valor['valor']);
							//$temp[$campo] = str_pad($d['day'],2,"0",STR_PAD_LEFT).'/'.str_pad($d['month'],2,"0",STR_PAD_LEFT).'/'.$d['year'];
							
							$temp[$campo] =date("d/m/Y", strtotime($valor['valor']));
						}
						else if($valor['tipo'] == 'file')
						{

							$temp[$campo]  = preg_replace('/temp_/','',$valor['valor'],1);

						}
						else{
							$temp[$campo] = $valor['valor'];
						}
						
						$temp[$campo] = stripcslashes($temp[$campo]);
				}
				
				$res->datos = $temp;
				
			}
			else
			{
				$res->exito   = false;
				$res->mensaje = $insertado ? $this->errorInsertar : $this->errorActualizar;
				$res->errores = $bd->ObtenerErrores();
			}
			return $res;
		}
	}
?>