<?php	
	class Documento extends Restful
	{			
		function Documento()
		{				
			$this->id    = "documento_id";
			$this->tabla = "documento";
								
			$this->exitoInsertar   = t("Award created successfully");
			$this->exitoActualizar = t("Award updated successfully");
			$this->errorInsertar   = t("Error creating Award");
			$this->errorActualizar = t("Error updating Award");
			$this->exitoListar     = t('Award list obtained successfully');
			$this->errorListar     = t('Error obtaining Award list');
			
			// aquí definimos los tipos de campos
			$this->campos = array(
				'documento_id'             => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid award ID'),'valor'=>'','lectura'=>false),
				'documento_titulo'         => array('tipo'=>'string','nulo'=>false,'msg'=>t('Invalid name'),'valor'=>null,'lectura'=>false),
				'documento_archivo'        => array('tipo'=>'file','nulo'=>true,'msg'=>t('Invalid doc'),'valor'=>'','lectura'=>false,  'ruta' => RUTA_DOCUMENTOS),
				'documento_miniatura'      => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid min'),'valor'=>'','lectura'=>true),
				'documento_categoria_id'   => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid min'),'valor'=>'','lectura'=>false),
				'documento_descripcion'    => array('tipo'=>'html','nulo'=>true,'msg'=>t('Invalid details'),'valor'=>'','lectura'=>false),
				'documento_usuario_id'     => array('tipo'=>'user_id','nulo'=>false,'msg'=>t('Invalid creator'),'valor'=>null,'lectura'=>false),
				'documento_permisos'       => array('tipo'=>'commasint','nulo'=>true,'msg'=>t('Invalid creator'),'valor'=>null,'lectura'=>false),
				'categoria_nombre'         => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid min'),'valor'=>'','lectura'=>true)
				);
			
			$this->relaciones = array(
			
				'categoria' => array (
					'tabla'          => 'categoria',
					'relacion'       => '1a1',
					'soloLectura'	 => true,
					'clavePrincipal' => 'documento_categoria_id',
					'claveAjena1'    => 'categoria_id',
					'claveAjena2'    => '',
					'campos'         => array(
						'categoria_id'         => array('tipo'=>'id','nulo'=>true,'msg'=>t('Invalid name'),'valor'=>'','lectura'=>true),
						'categoria_nombre'     => array('tipo'=>'string','nulo'=>false,'msg'=>t('Invalid name'),'valor'=>'','lectura'=>true)
					)
				)
			);	
		}
		
		protected function runAction($action, $params)
		{
			$adminDocs = Usuario::TienePermiso('administrar_documentos')->exito;
			
			switch($action)
			{
				case 'read':
					if(Usuario::EstaIdentificado()){
						$idUsuario      = Usuario::TienePermiso('ver_todos_documentos')->exito ? 1 : Usuario::IdUsuario();
						$filtros        = isset($_REQUEST["filtros"])?$_REQUEST["filtros"]:"";
						$limit          = isset($_REQUEST["limit"])?$_REQUEST["limit"]:SELECT_LIMIT;
						$start          = isset($_REQUEST["start"])?$_REQUEST["start"]:0;
						$sort           = isset($_REQUEST["sort"])?$_REQUEST["sort"]:"";
						$this->Listar($idUsuario,$filtros,$start,$limit,$sort)->ImprimirJson();	
					} else $this->getPermError()->ImprimirJson();
				break;
				
				case 'add':
					$this->add($adminDocs);
				break;
				
				case 'destroy':
					$this->destroy($adminDocs);
				break;
				
				case 'upload':
					if($adminDocs){
						$nombre = isset($_REQUEST["nombre"])?$_REQUEST["nombre"]:"";
						parent::SubirArchivo($nombre, true, true, DOCUMENTOS_PERMITIDOS, TAM_MAX, RUTA_DOCUMENTOS, URL_DOCUMENTOS)->ImprimirJson();							
					}
					else $this->getPermError()->ImprimirJson();
				
				break;	
				
				case 'download':
					$idDocumento = isset($_REQUEST["id"])?$_REQUEST["id"]:"";
					$android = isset($_REQUEST["android"])?true:false;
					Documento::DescargarArchivo($idDocumento,$android);
				break;
				
				default:
					$this->getInvalidError()->ImprimirJson();
				break;
			}
		}
		
		protected static function ObtenerArchivo($idDocumento)
		{
			$bd 		= BD::Instancia();
			$consulta 	= "";
			$res        = new Comunicacion();
			
			$consulta = "SELECT documento_archivo FROM documento WHERE documento_id=".intval($idDocumento).";";	
			$datos = $bd->Ejecutar($consulta);
			$fila = $bd->ObtenerFila($datos);
			
			return $fila['documento_archivo'];				
		}
		
		protected static function TienePermiso($idDocumento)
		{
			$bd = BD::Instancia();
			
			// comprobamos que tenga permiso
			if(!Usuario::EstaIdentificado()) return false;
			$identificador = $_SESSION["usuario"];
			
			// si no coincide el hash no damos permisos
			if( md5($identificador["usuario_id"].BACKOFFICE_CLAVE) != $identificador["hash"] )
			{
				return false;
			}
	
			// si tiene el rol de administrador siempre tiene permiso
			$consulta = "SELECT COUNT(*) FROM usuario WHERE usuario_id= '". intval($identificador["usuario_id"]) ."' AND usuario_rol_id = '1' ";
			if ( $bd->ContarFilas($consulta) > 0 )
			{
				return true;
			}
			
			$consulta = "SELECT usuario_rol_id FROM usuario WHERE usuario_id=".intval($identificador["usuario_id"]).";";
			$fila = $bd->ObtenerFila($consulta);
			
			$consulta = "SELECT COUNT(*) FROM documento WHERE documento_id = '".intval($idDocumento)."' AND ( 
			              FIND_IN_SET('". intval($identificador["usuario_id"]) ."',documento_permisos) > 0 OR
						  FIND_IN_SET('". $fila["usuario_rol_id"] ."',documento_permisos_roles) > 0
						 )";
			
			if ( $bd->ContarFilas($consulta) > 0 )
			{
				return true;
			}	
			
			return false;
		}
		
		protected static function DescargarArchivo($idDocumento, $android){
		
			$nombre = Documento::ObtenerArchivo($idDocumento);
			$ruta = RUTA_DOCUMENTOS;

			$nombreReal = strstr($nombre, '_');
			$nombreReal = strlen($nombreReal) > 1 ? substr( $nombreReal, 1 ) : $nombre;
			
			if (file_exists($ruta.$nombre) && Documento::TienePermiso($idDocumento))
			{
				if($android)
				{
					header('Location: '.URL_ANDROID_DOCUMENTOS.$nombre);
				}		
				else{		
					readfile($fichero);($ruta.$nombre);
					@header("Content-type: application/file");
					@header("Content-Disposition: attachment; filename='".strtolower($nombreReal)."'");
					echo file_get_contents($ruta.$nombre);
				}
			}else{
				header('HTTP/1.0 404 Not Found');
				echo "<h1>404 Not Found</h1>";
				echo "The file that you have requested could not be found";
				exit();	
			}
		}
		
		// elimina la id de usuario de la lista de permisos de los documentos
		protected static function EliminarPermiso($idUsuario)
		{
			$bd = BD::Instancia();
			$isOk = true;
			
			$consulta = "SELECT documento_id, documento_permisos FROM documento WHERE FIND_IN_SET('".intval($idUsuario)."',documento_permisos);";	
			$datos = $bd->Ejecutar($consulta);
			
			if( $bd->ObtenerErrores() == '' )
			{
				// obtenemos una lista con todos los registros
				while($fila = $bd->ObtenerFila($datos))
				{
					$permisos = explode(",", $fila['documento_permisos']);
					$permisos = array_diff($permisos, array($idUsuario));
					
					$bd->Ejecutar("UPDATE documento SET documento_permisos='". implode(',', $permisos)."' WHERE documento_id='".$fila['documento_id']."' ;");
					if( $bd->ObtenerErrores() != '' ){
						$isOk = false;
						break;
					}
				}
			}
			else{
				$isOk = false;
			}
			
			return $isOk;
		}
		
		// elimina la id de rol de la lista de permisos de los documentos
		protected static function EliminarPermisoRol($idRol)
		{
			$bd = BD::Instancia();
			$isOk = true;
			
			$consulta = "SELECT documento_id, documento_permisos_roles FROM documento WHERE FIND_IN_SET('".intval($idRol)."',documento_permisos_roles);";	
			$datos = $bd->Ejecutar($consulta);
			
			if( $bd->ObtenerErrores() == '' )
			{
				// obtenemos una lista con todos los registros
				while($fila = $bd->ObtenerFila($datos))
				{
					$permisos = explode(",", $fila['documento_permisos_roles']);
					$permisos = array_diff($permisos, array($idRol));
					
					$bd->Ejecutar("UPDATE documento SET documento_permisos_roles='". implode(',', $permisos)."' WHERE documento_id='".$fila['documento_id']."' ;");
					if( $bd->ObtenerErrores() != '' ){
						$isOk = false;
						break;
					}
				}
			}
			else{
				$isOk = false;
			}
			
			return $isOk;
		}
		
		protected function Guardar()
		{
			$r = parent::Guardar(true,true);
			$r->datos['documento_miniatura'] = ObjetoBD::ObtenerMiniatura($r->datos['documento_archivo']);
			$r->datos['categoria_nombre']    = Categoria::ObtenerNombre($r->datos['documento_categoria_id']);
			
			return $r;
		}
		
		protected function Listar($idUsuario, $filtros = null,$start=null,$limit = null,$sort = null)
		{			
			$filtroBusqueda = $idUsuario != 1 ? "AND  ( FIND_IN_SET('".intval($idUsuario)."',documento_permisos) OR documento_permisos = '".intval($idUsuario)."' )" : "";		
			
			$consulta =" SELECT * FROM documento LEFT JOIN categoria ON documento_categoria_id = categoria_id 
						 WHERE 1 ".$filtroBusqueda;
			
			$r = parent::Listar($consulta, CACHE_ACTIVADO, $filtros, $start, $limit, $sort);
			

			for($i = 0, $size = count($r->datos); $i < $size; ++$i) {
				$r->datos[$i]['documento_miniatura'] = ObjetoBD::ObtenerMiniatura($r->datos[$i]['documento_archivo']);
			}			
			
			return $r;
		}
	}