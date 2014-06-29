<?php	
	class Seccion extends ObjetoBD
	{			
		function Seccion()
		{				
			$this->id    = "seccion_id";
			$this->tabla = "seccion";
								
			$this->exitoInsertar   = t("Section created successfully");
			$this->exitoActualizar = t("Section updated successfully");
			$this->errorInsertar   = t("Error creating Section");
			$this->errorActualizar = t("Error updating Section");
			$this->exitoListar     = t('Section list obtained successfully');
			$this->errorListar     = t('Error obtaining Section list');
			
			// aquí definimos los tipos de campos
			$this->campos = array(
				'seccion_id'                        => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid Section ID'),'valor'=>'','lectura'=>false),
				'seccion_nombre'                    => array('tipo'=>'string','nulo'=>false,'msg'=>t('Invalid name'),'valor'=>null,'lectura'=>false),
				'seccion_padre_id'                  => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid father id'),'valor'=>0,'lectura'=>false)
			);			
		}
		
		public function Guardar($seccionId,$seccionPadreId, $seccionNombre)
		{					
			$this->campos["seccion_id"]["valor"] = $seccionId;
			$this->campos["seccion_padre_id"]["valor"] = $seccionPadreId;
			$this->campos["seccion_nombre"]["valor"] = $seccionNombre;
			
			// itentamos guardar
			$res = parent::Guardar(true,false);
			
			// si el guardado ha sido correcto eliminamos los archivos de cache
			if($res->exito)
			{
				Cache::Eliminar("listaSecciones");
				Cache::Eliminar("arbolSecciones");
			}
			
			return $res;
		}
		
		public function EliminarDato($idseccion)
		{
			$bd = BD::Instancia();
			$consulta = "";
			$res = new Comunicacion();
			
			parent::EliminarDato($idseccion);
			
			// si tenía hijos los ponemos sin padre
			$consulta = "UPDATE seccion SET seccion_padre_id ='0' WHERE seccion_padre_id='".intval($idseccion)."'";
			$bd->Ejecutar($consulta);
				
			if( $bd->ObtenerErrores() == "" )
			{
				$res->exito = true;
				$res->mensaje = t("Success");
				$res->errores = "";
					
				// si se ha eliminado correctamente eliminamos los archivos de cache
				Cache::Eliminar("listaSecciones");
				Cache::Eliminar("arbolSecciones");
			}
			else
			{
				$res->exito = false;
				$res->mensaje = $bd->ObtenerErrores();
				$res->errores = t("Delete relations operation failed");
			}
			return $res;
		}
		
		private static function ConstruirArbol($consulta,$refrescar = false)
		{		
			$resultado = false;
			$res = new Comunicacion();
			
			if($refrescar)
			{
				Cache::Eliminar("arbolSecciones");
			}else $resultado = Cache::Obtener("arbolSecciones");
			
			if(!$resultado)
			{
				$bd = BD::Instancia();
				$datos = $bd->Ejecutar($consulta);
				$data = array();
				
				if( $bd->ObtenerErrores() == '' )
				{
					while($row = $bd->ObtenerFila($datos))
					{
						array_push($data,array(
						"id" => $row["seccion_id"],
						"idParent" => $row["seccion_padre_id"],
						"text" => $row["seccion_nombre"],
						"expanded" => true,
					
						"seccion_id" => $row["seccion_id"],
						"seccion_nombre" => $row["seccion_nombre"],
						"seccion_padre_id" => $row["seccion_padre_id"]
						));
					}
					
					// Crear el árbol en formato JSON
					$tree = new TreeExtJS();
					for($i=0;$i<count($data);$i++)
					{
						$category = $data[$i];
						$tree->addChild($category,$category["idParent"]);
					}

					$resultado = $tree->GetTree();
					Cache::Guardar("arbolSecciones",$resultado);
				}
				else
				{
					$res->exito = false;
					$res->mensaje = t('Error');
					$res->errores = $bd->ObtenerErrores();	
					return $res;
				}
			}
			
			$res->exito = true;
			$res->mensaje = t('Success');
			$res->errores = '';
			$res->datos = $resultado;
					
			return $res;
		}
		
		public static function ListaIdentada()
		{
			$res = new Comunicacion();
			$resultado = Cache::Obtener("listaSecciones");
			
			if(!$resultado)
			{
				$resultado = TreeExtJS::ListaIdentada("SELECT * FROM seccion ORDER BY seccion_padre_id ASC",'seccion_id', 'seccion_padre_id', 'seccion_nombre');
				Cache::Guardar("listaSecciones",$resultado);
			}
			
			$res->exito = true;
			$res->mensaje = t('Success');
			$res->errores = '';
			$res->datos = $resultado;
			return $res;
		}
		
		public function Listar($filtros=null,$start=null,$limit = null, $sort=null,$refrescar=false)
		{		
			$res = new Comunicacion();
			
			$consulta = "SELECT * FROM seccion ORDER BY seccion_padre_id ASC";
			return Seccion::ConstruirArbol($consulta,$refrescar);
		}
	}