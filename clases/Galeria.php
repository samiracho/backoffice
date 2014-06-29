<?php	
	class Categoria extends Restful
	{
		function Categoria()
		{
			$this->id              = "categoria_id";
			$this->tabla           = "categoria";
			$this->tablasCache     = array('documento');
			$this->exitoListar     = t("User list obtained successfully");
			$this->errorListar     = t("Error obtaining user list");
			$this->exitoInsertar   = t("User created successfully");
			$this->exitoActualizar = t("User updated successfully");
			$this->errorInsertar   = t("Error creating user");
			$this->errorActualizar = t("Error updating user");	
			
			$this->campos = array(
				'categoria_id'        => array('tipo'=>'id','nulo'=>true,'msg'=>t('Invalid cat ID'),'valor'=>'','lectura'=>false),
				'categoria_nombre'    => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid cat name'),'valor'=>'','lectura'=>false)
			);
		}
		
		protected function runAction($action, $params)
		{
			$adminDocs = Usuario::TienePermiso('administrar_categorias')->exito;
			
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
				
				default:
					$this->getInvalidError()->ImprimirJson();
				break;
			}
		}
		
		public static function ObtenerNombre($idCategoria)
		{
			$bd 		= BD::Instancia();
			$consulta   = " SELECT categoria_nombre FROM categoria WHERE categoria_id='".intval($idCategoria)."';";
			$resultado  = $bd->ObtenerResultados($consulta);
			
			if( array_key_exists ('categoria_nombre',$resultado[0]) )
			
			return ($resultado[0]['categoria_nombre']);
		}
		
		protected function Listar($filtros = null,$start=null,$limit = null,$sort = null)
		{		
			$consulta =" SELECT * FROM categoria WHERE 1 ";
			
			$r = parent::Listar($consulta, CACHE_ACTIVADO, $filtros, $start, $limit, $sort);	
			return $r;
		}
	}
?>
