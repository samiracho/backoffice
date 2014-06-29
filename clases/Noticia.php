<?php	
	class Noticia extends Restful
	{			
		private static $plantilla = "noticias.htm";
		
		function Noticia()
		{				
			$this->id        = "noticia_id";
			$this->tabla     = "noticia";
								
			$this->exitoInsertar   = t("Article created successfully");
			$this->exitoActualizar = t("Article updated successfully");
			$this->errorInsertar   = t("Error creating Article");
			$this->errorActualizar = t("Error updating Article");
			$this->exitoListar     = t('Article list obtained successfully');
			$this->errorListar     = t('Error obtaining Article list');
			
			// aquí definimos los tipos de campos
			$this->campos = array(
				'noticia_id'         => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid award ID'),'valor'=>'','lectura'=>false),
				'noticia_usuario_id' => array('tipo'=>'user_id','nulo'=>false,'msg'=>t('Invalid id User'),'valor'=>null,'lectura'=>false),
				'noticia_titulo'     => array('tipo'=>'string','nulo'=>false,'msg'=>t('Invalid name'),'valor'=>null,'lectura'=>false),
				'noticia_cuerpo'     => array('tipo'=>'html','nulo'=>true,'msg'=>t('Invalid details'),'valor'=>'','lectura'=>false),
				'noticia_publicada'  => array('tipo'=>'checkbox','nulo'=>true,'msg'=>t('Invalid published'),'valor'=>'','lectura'=>false),
				'noticia_portada'    => array('tipo'=>'checkbox','nulo'=>true,'msg'=>t('Invalid front'),'valor'=>'','lectura'=>false),
				'noticia_fecha'      => array('tipo'=>'date','nulo'=>true,'msg'=>t('Invalid date'),'valor'=>'','lectura'=>false),
				'noticia_imagen'     => array('tipo'=>'file','nulo'=>true,'msg'=>t('Invalid image'),'valor'=>'','lectura'=>false, 'ruta' => RUTA_DOCUMENTOS),
				'noticia_ultimamod'  => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid name'),'valor'=>'','lectura'=>true),
				'noticia_categoria_id' => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid min'),'valor'=>'','lectura'=>false),
				'categoria_nombre'     => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid min'),'valor'=>'','lectura'=>true)
			);

			$this->relaciones = array(
			
				'usuario' => array (
					'tabla'         => 'usuario',
					'relacion'      => '1a1',
					'clavePrimaria' => 'noticia_usuario_id',
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
					'clavePrincipal' => 'noticia_categoria_id',
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
			$adminDocs = Usuario::TienePermiso('administrar_noticias')->exito;
			
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
						parent::SubirArchivo($nombre, true, true, IMAGENES_PERMITIDAS, TAM_MAX, RUTA_DOCUMENTOS, URL_DOCUMENTOS)->ImprimirJson();
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
			
			$consulta = "SELECT DISTINCT categoria_id, categoria_nombre FROM categoria INNER JOIN noticia ON categoria_id=noticia_categoria_id ORDER BY categoria_nombre ASC";			
			
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
		
		public static function ObtenerNoticia($noticiaId=1)
		{
			$bd = BD::Instancia();
			$noticias = array();
			
			$consulta = "SELECT noticia_id, noticia_titulo, noticia_imagen, DATE_FORMAT(noticia_fecha, '".FORMATO_FECHA_MYSQL."') AS noticia_fechapub, noticia_cuerpo, usuario_login AS noticia_usuario, categoria_nombre, noticia_categoria_id FROM noticia INNER JOIN usuario ON usuario_id=noticia_usuario_id  INNER JOIN categoria ON categoria_id=noticia_categoria_id
			             WHERE noticia_publicada = '1' AND noticia_id=".$noticiaId."";

			$datos = $bd->Ejecutar($consulta);
			if( $bd->ObtenerErrores() == '' )
			{	
				// obtenemos ula noticia
				return $bd->ObtenerFila($datos);
			}
		}
		
		public static function ObtenerUltimasNoticias($limite = 3)
		{
			$bd = BD::Instancia();
			$noticias = array();
			
			$consulta = "SELECT noticia_id, noticia_titulo, noticia_imagen, DATE_FORMAT(noticia_fecha, '".FORMATO_FECHA_MYSQL."') AS noticia_fechapub, noticia_cuerpo, usuario_login AS noticia_usuario, categoria_nombre, noticia_categoria_id FROM noticia INNER JOIN usuario ON usuario_id=noticia_usuario_id  INNER JOIN categoria ON categoria_id=noticia_categoria_id
			             WHERE noticia_publicada = '1' ORDER BY noticia_fecha DESC LIMIT 0,".$limite;

						 
			$datos = $bd->Ejecutar($consulta);
			if( $bd->ObtenerErrores() == '' )
			{	
				// obtenemos una lista con todos los registros
				while($fila = $bd->ObtenerFila($datos))
				{
					array_push($noticias, $fila); 
				}
			}
			return $noticias;
		}
		
		public static function ObtenerNoticias($numPagina=1, $categoriaId = null, $longitudResumen = 300, $resultadosPorPag = 5,$url='?')
		{
			include 'HTMLCutter.php';
			
			$bd = BD::Instancia();
			$noticias = array();
			
			$start = 0;
			$limit = $resultadosPorPag;
			
			if($numPagina > 1)
			{
				$start = ( ($numPagina -1)*$resultadosPorPag );
			}
			
			$filtroConsulta = ($categoriaId != null) ? "AND noticia_categoria_id =".$categoriaId : "";
			
			
			$consulta = "SELECT noticia_id, noticia_titulo, noticia_imagen, DATE_FORMAT(noticia_fecha, '".FORMATO_FECHA_MYSQL."') AS noticia_fechapub, noticia_cuerpo, usuario_login AS noticia_usuario, categoria_nombre, noticia_categoria_id FROM noticia INNER JOIN usuario ON usuario_id=noticia_usuario_id  INNER JOIN categoria ON categoria_id=noticia_categoria_id
			             WHERE noticia_publicada = '1' ".$filtroConsulta." ORDER BY noticia_fecha DESC LIMIT ".$start.",".$limit."";			
			
			$datos = $bd->Ejecutar($consulta);
			if( $bd->ObtenerErrores() == '' )
			{	
				// obtenemos una lista con todos los registros
				while($fila = $bd->ObtenerFila($datos))
				{
					array_push($noticias, $fila); 
				}
			}

			for($i = 0, $size = count($noticias); $i < $size; ++$i) {
				if($noticias[$i]['noticia_imagen']!=""){
					$noticias[$i]['noticia_miniatura'] = ObjetoBD::ObtenerMiniatura($noticias[$i]['noticia_imagen']);
					$noticias[$i]['noticia_imagen']    = URL_IMAGENES.'/'.$noticias[$i]['noticia_imagen'];
				}
				
				// vamos a construir el resumen
				$resumen = HTMLCutter::cut($noticias[$i]['noticia_cuerpo'], $longitudResumen);		
				$noticias[$i]['noticia_resumen']   =  strlen($noticias[$i]['noticia_cuerpo']) < $longitudResumen ? $noticias[$i]['noticia_cuerpo'] : $resumen;
			}					
			
			$url = ($categoriaId != null) ? "?cat=".$categoriaId."&" : $url;
			
			// agregamos el paginador
			$paginador = parent::ConstruirPaginador("SELECT COUNT(*) FROM noticia INNER JOIN categoria ON categoria_id=noticia_categoria_id WHERE noticia_publicada='1' ".$filtroConsulta, $numPagina, $resultadosPorPag, $url );
			
			return array('noticias'=>$noticias, 'paginador' => $paginador );
		}
		
		public function Listar($filtros=null,$start=null,$limit = null,$sort=null)
		{				
			$consulta = "SELECT N.*, DATE_FORMAT(noticia_fecha, '".FORMATO_FECHA_MYSQL."') AS noticia_fecha, noticia_categoria_id, categoria_nombre, usuario_nombre, usuario_apellidos, usuario_login, noticia_fecha AS sortnoticia_fecha 
			             FROM noticia N LEFT JOIN usuario ON noticia_usuario_id = usuario_id LEFT JOIN categoria ON noticia_categoria_id = categoria_id WHERE 1 ";
			
			
			$r = parent::Listar($consulta, false, $filtros,$start,$limit,$sort);
			return $r;
		}
	}
