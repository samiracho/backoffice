<?php	
	class Foto extends Restful
	{
		function Foto()
		{
			$this->id              = "foto_id";
			$this->tabla           = "foto";
			$this->tablasCache     = array('documento');
			$this->exitoListar     = t("Photos list obtained successfully");
			$this->errorListar     = t("Error obtaining photos list");
			$this->exitoInsertar   = t("Photo created successfully");
			$this->exitoActualizar = t("Photo updated successfully");
			$this->errorInsertar   = t("Error creating photo");
			$this->errorActualizar = t("Error updating photo");	
			
			$this->campos = array(
				'foto_id'          => array('tipo'=>'id','nulo'=>true,'msg'=>t('Invalid cat ID'),'valor'=>'','lectura'=>false),
				'foto_titulo'      => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid photo name'),'valor'=>'','lectura'=>false),
				'foto_imagen'      => array('tipo'=>'file','nulo'=>true,'msg'=>t('Invalid image'),'valor'=>'','lectura'=>false,  'ruta' => RUTA_DOCUMENTOS),
				'foto_miniatura'   => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid min'),'valor'=>'','lectura'=>true),
				'foto_proyecto_id' => array('tipo'=>'id','nulo'=>true,'msg'=>t('Invalid cat ID'),'valor'=>'','lectura'=>false)
			);
		}
		
		protected function runAction($action, $params)
		{
			$adminDocs = Usuario::TienePermiso('administrar_fotos')->exito;
			
			switch($action)
			{
				case 'read':
					$this->read();
				break;
				
				case 'add':
					$this->GuardarFotos($adminDocs)->ImprimirJson();
				break;
				
				case 'upload':
					if($adminDocs){
						$nombre = isset($_REQUEST["nombre"])?$_REQUEST["nombre"]:"";
						parent::SubirArchivo($nombre, true, true, IMAGENES_PERMITIDAS, TAM_MAX, RUTA_DOCUMENTOS, URL_DOCUMENTOS)->ImprimirJson();
					}
					else $this->getPermError()->ImprimirJson();	
				break;
				
				case 'getByProject':
						$res = Comunicacion::DecodificarJson($_REQUEST["filter"],true);
						$idProyecto = $res[0]->{'value'};
						return $this->ObtenerFotosProyecto($idProyecto)->ImprimirJson();
				break;
				
				case 'destroy':
					$this->destroy($adminDocs);
				break;
				
				default:
					$this->getInvalidError()->ImprimirJson();
				break;
			}
		}
		
		public static function ObtenerNombre($idFoto)
		{
			$bd 		= BD::Instancia();
			$consulta   = " SELECT foto_titulo FROM foto WHERE foto_id='".intval($idFoto)."';";
			$resultado  = $bd->ObtenerResultados($consulta);
			
			if( array_key_exists ('foto_titulo',$resultado[0]) )
			
			return ($resultado[0]['foto_titulo']);
		}
		
		
		protected function GuardarFotos($permiso = null)
		{
			if (!$permiso)return $this->getPermError();
			
			$res = new Comunicacion();
			$lectura = new Comunicacion();			
			$lectura->LeerJson();
			
			if(is_array($lectura->datos) && sizeof($lectura->datos) > 0 )
			{			
				$class = new ReflectionClass(get_class($this));
				
				foreach ($lectura->datos as $datos)
				{				
					$foto = $class->newInstanceArgs();
					//$foto = new Foto();
				
					foreach ($datos as $campo => $valor) {
						if (array_key_exists($campo, $this->campos)) {						
							$foto->campos[$campo]['valor'] = $valor;
						}
							
						if ($foto->relaciones != ""){
							foreach ($foto->relaciones as $rel => $val) {
								if (array_key_exists($campo, $foto->relaciones[$rel]['campos'])) {						
									$foto->relaciones[$rel]['campos'][$campo]['valor'] = $valor;
								}
							}
						}
					}

					$linea = $foto->Guardar(true, false);
					$res->datos[] = $linea->datos;
					$res->exito = $linea->exito;
				}
			} else $res = $this->Guardar(true, true);
			
			return $res;
		}	
		
		protected function ObtenerFotosProyecto($idProyecto=1)
		{
			$consulta =" SELECT * FROM foto WHERE foto_proyecto_id='".intval($idProyecto)."' ";
			
			
			$r = parent::Listar($consulta, false, null, null, null, null);	
			
			for($i = 0, $size = count($r->datos); $i < $size; ++$i) {
				$r->datos[$i]['foto_miniatura'] = ObjetoBD::ObtenerMiniatura($r->datos[$i]['foto_imagen']);
			}				
			return $r;
		}
		
		protected function Listar($filtros = null,$start=null,$limit = null,$sort = null)
		{		
			$consulta =" SELECT * FROM foto WHERE 1 ";
			
			$r = parent::Listar($consulta, CACHE_ACTIVADO, $filtros, $start, $limit, $sort);

			for($i = 0, $size = count($r->datos); $i < $size; ++$i) {
				$r->datos[$i]['foto_miniatura'] = ObjetoBD::ObtenerMiniatura($r->datos[$i]['foto_imagen']);
			}					
			return $r;
		}
	}
?>
