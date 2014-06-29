<?php	
	class Proyecto extends Restful
	{			
		private static $plantilla = "proyectos.htm";
		
		function Proyecto()
		{				
			$this->id        = "proyecto_id";
			$this->tabla     = "proyecto";
								
			$this->exitoInsertar   = t("Article created successfully");
			$this->exitoActualizar = t("Article updated successfully");
			$this->errorInsertar   = t("Error creating Article");
			$this->errorActualizar = t("Error updating Article");
			$this->exitoListar     = t('Article list obtained successfully');
			$this->errorListar     = t('Error obtaining Article list');
			
			// aquí definimos los tipos de campos
			$this->campos = array(
				'proyecto_id'           => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid award ID'),'valor'=>'','lectura'=>false),
				'proyecto_usuario_id'   => array('tipo'=>'user_id','nulo'=>false,'msg'=>t('Invalid id User'),'valor'=>null,'lectura'=>false),
				'proyecto_titulo'       => array('tipo'=>'string','nulo'=>false,'msg'=>t('Invalid name'),'valor'=>null,'lectura'=>false),
				'proyecto_cuerpo'       => array('tipo'=>'html','nulo'=>true,'msg'=>t('Invalid details'),'valor'=>'','lectura'=>false),
				'proyecto_publicada'    => array('tipo'=>'checkbox','nulo'=>true,'msg'=>t('Invalid published'),'valor'=>'','lectura'=>false),
				'proyecto_fecha'        => array('tipo'=>'date','nulo'=>true,'msg'=>t('Invalid date'),'valor'=>'','lectura'=>false),
				'proyecto_ficha'        => array('tipo'=>'file','nulo'=>true,'msg'=>t('Invalid image'),'valor'=>'','lectura'=>false, 'ruta' => RUTA_DOCUMENTOS),
				'proyecto_ultimamod'    => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid name'),'valor'=>'','lectura'=>true),
				'proyecto_categoria_id' => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid min'),'valor'=>'','lectura'=>false),
				'categoria_nombre'      => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid min'),'valor'=>'','lectura'=>true)
			);

			$this->relaciones = array(
			
				'usuario' => array (
					'tabla'         => 'usuario',
					'relacion'      => '1a1',
					'clavePrimaria' => 'proyecto_usuario_id',
					'claveAjena1'   => 'usuario_id',
					'claveAjena2'   => '',
					'soloLectura' => true,
					'campos'        => array(
						'usuario_id'         => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid name'),'valor'=>'','lectura'=>true),
						'usuario_nombre'     => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid name'),'valor'=>'','lectura'=>true),
						'usuario_apellidos'  => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid name'),'valor'=>'','lectura'=>true),
						'usuario_login'      => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid name'),'valor'=>'','lectura'=>true)
					)
				),
				'categoria' => array (
					'tabla'          => 'categoria',
					'relacion'       => '1a1',
					'soloLectura'	 => true,
					'clavePrincipal' => 'proyecto_categoria_id',
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
			$adminDocs = Usuario::TienePermiso('administrar_proyectos')->exito;
			
			switch($action)
			{
				case 'read':
					$this->read();
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
				
				default:
					$this->getInvalidError()->ImprimirJson();
				break;
			}
		}
		
		public static function ObtenerCategorias()
		{
			$bd = BD::Instancia();
			$categorias = array();
			
			$consulta = "SELECT DISTINCT categoria_id, categoria_nombre FROM categoria INNER JOIN proyecto ON categoria_id=proyecto_categoria_id ORDER BY categoria_nombre ASC";			
			
			$datos = $bd->Ejecutar($consulta);
			if( $bd->ObtenerErrores() == '' )
			{	
				// obtenemos una lista con todos los registros
				while($fila = $bd->ObtenerFila($datos))
				{
					array_push($categorias, $fila); 
				}
			}
			
			return $categorias;
		
		}
		
		public static function ObtenerProyecto($proyectoId=1)
		{
			$bd = BD::Instancia();
			$proyecto = array();
			
			$consulta = "SELECT proyecto_id, proyecto_titulo, proyecto_ficha, DATE_FORMAT(proyecto_fecha, '".FORMATO_FECHA_MYSQL."') AS proyecto_fechapub, proyecto_cuerpo, usuario_login AS proyecto_usuario, categoria_nombre, proyecto_categoria_id FROM proyecto INNER JOIN usuario ON usuario_id=proyecto_usuario_id  INNER JOIN categoria ON categoria_id=proyecto_categoria_id
			             WHERE proyecto_publicada = '1' AND proyecto_id=".$proyectoId."";

			$datos = $bd->Ejecutar($consulta);
			if( $bd->ObtenerErrores() == '' )
			{	
				// obtenemos el proyecto
				$proyecto = $bd->ObtenerFila($datos);
				$proyecto['fotos'] = Proyecto::ObtenerFotos($proyectoId);
				return $proyecto;
			}
			else return null;		
		}
		
		public static function ObtenerFotos($proyectoId=1)
		{
			$bd = BD::Instancia();
			$fotos = array();
			
			$consulta = "SELECT * FROM foto WHERE foto_proyecto_id=".$proyectoId."";

			$datos = $bd->Ejecutar($consulta);
			if( $bd->ObtenerErrores() == '' )
			{	
				// obtenemos una lista con todos los registros
				while($fila = $bd->ObtenerFila($datos))
				{
					array_push($fotos, $fila); 
				}
			}
			return $fotos;
		}
		
		public static function ObtenerUltimasProyectos($limite = 3)
		{
			$bd = BD::Instancia();
			$proyectos = array();
			
			$consulta = "SELECT proyecto_id, proyecto_titulo, proyecto_ficha, DATE_FORMAT(proyecto_fecha, '".FORMATO_FECHA_MYSQL."') AS proyecto_fechapub, proyecto_cuerpo, usuario_login AS proyecto_usuario, categoria_nombre, proyecto_categoria_id FROM proyecto INNER JOIN usuario ON usuario_id=proyecto_usuario_id  INNER JOIN categoria ON categoria_id=proyecto_categoria_id
			             WHERE proyecto_publicada = '1' ORDER BY proyecto_fecha DESC LIMIT 0,".$limite;

						 
			$datos = $bd->Ejecutar($consulta);
			if( $bd->ObtenerErrores() == '' )
			{	
				// obtenemos una lista con todos los registros
				while($fila = $bd->ObtenerFila($datos))
				{
					array_push($proyectos, $fila); 
				}
			}
			return $proyectos;
		}
		
		public static function ObtenerProyectos($numPagina=1, $categoriaId = null, $longitudResumen = 300, $resultadosPorPag = 0,$url='?')
		{
			include 'HTMLCutter.php';
			
			$bd = BD::Instancia();
			$proyectos = array();
			
			$start = 0;
			$limit = $resultadosPorPag;
			
			if($numPagina > 1){
				$start = ( ($numPagina -1)*$resultadosPorPag );
			}
			
			$filtroConsulta = ($categoriaId != null) ? "AND proyecto_categoria_id =".$categoriaId : "";		
			$aplicarLimite = $resultadosPorPag == 0 ? "" : "DESC LIMIT ".$start.",".$limit."";
			
			$consulta = "SELECT proyecto_id, proyecto_titulo, proyecto_ficha, DATE_FORMAT(proyecto_fecha, '".FORMATO_FECHA_MYSQL."') AS proyecto_fechapub, proyecto_cuerpo, usuario_login AS proyecto_usuario, categoria_nombre, proyecto_categoria_id FROM proyecto INNER JOIN usuario ON usuario_id=proyecto_usuario_id  INNER JOIN categoria ON categoria_id=proyecto_categoria_id
			             WHERE proyecto_publicada = '1' ".$filtroConsulta." ORDER BY proyecto_fecha ".$aplicarLimite;			
			
			$datos = $bd->Ejecutar($consulta);
			if( $bd->ObtenerErrores() == '' )
			{	
				// obtenemos una lista con todos los registros
				while($fila = $bd->ObtenerFila($datos))
				{
					array_push($proyectos, $fila); 
				}
			}

			for($i = 0, $size = count($proyectos); $i < $size; ++$i) {
			
				// vamos a construir el resumen
				$resumen = HTMLCutter::cut($proyectos[$i]['proyecto_cuerpo'], $longitudResumen);		
				$proyectos[$i]['proyecto_resumen']   =  strlen($proyectos[$i]['proyecto_cuerpo']) < $longitudResumen ? $proyectos[$i]['proyecto_cuerpo'] : $resumen;
				$proyectos[$i]['proyecto_fotos'] = Proyecto::ObtenerFotos($proyectos[$i]['proyecto_id']);
			}					
			
			$url = ($categoriaId != null) ? "?cat=".$categoriaId."&" : $url;
			
			// agregamos el paginador
			$paginador = $resultadosPorPag== 0 ? null : parent::ConstruirPaginador("SELECT COUNT(*) FROM proyecto INNER JOIN categoria ON categoria_id=proyecto_categoria_id WHERE proyecto_publicada='1' ".$filtroConsulta, $numPagina, $resultadosPorPag, $url );
			
			return array('proyectos'=>$proyectos, 'paginador' => $paginador );
		}
		
		public function Listar($filtros=null,$start=null,$limit = null,$sort=null)
		{				
			$consulta = "SELECT N.*, DATE_FORMAT(proyecto_fecha, '".FORMATO_FECHA_MYSQL."') AS proyecto_fecha, proyecto_categoria_id, categoria_nombre, usuario_nombre, usuario_apellidos, usuario_login, proyecto_fecha AS sortproyecto_fecha 
			             FROM proyecto N LEFT JOIN usuario ON proyecto_usuario_id = usuario_id LEFT JOIN categoria ON proyecto_categoria_id = categoria_id WHERE 1 ";
			
			
			$r = parent::Listar($consulta, false, $filtros,$start,$limit,$sort);
			return $r;
		}
	}
