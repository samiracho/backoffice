<?php	
	class Rol extends Restful
	{
		protected $rol;					    // rol asignado al rol
		
		function Rol()
		{
			$this->id     = "rol_id";
			$this->tabla  = "rol";
			$this->tablasCache     = array('usuario');
			$this->exitoListar     = t("User list obtained successfully");
			$this->errorListar     = t("Error obtaining user list");
			$this->exitoInsertar   = t("User created successfully");
			$this->exitoActualizar = t("User updated successfully");
			$this->errorInsertar   = t("Error creating user");
			$this->errorActualizar = t("Error updating user");	
			
			$this->campos = array(
				'rol_id'          => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid role ID'),'valor'=>'','lectura'=>false),
				'rol_nombre'      => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid user name'),'valor'=>'','lectura'=>false),
				'rol_basico'      => array('tipo'=>'checkbox','nulo'=>true,'msg'=>t('Invalid basic'),'valor'=>'','lectura'=>true),
				'rol_descripcion' => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid role description'),'valor'=>'','lectura'=>false),
				'rol_permisos'    => array('tipo'=>'commasint','nulo'=>true,'msg'=>t('Invalid role perms'),'valor'=>'','lectura'=>false),
				'permiso_nombre'  => array('tipo'=>'string','nulo'=>true,'msg'=>'','valor'=>'','lectura'=>true)
			);
		}

		protected function runAction($action, $params)
		{
			$adminDocs = Usuario::TienePermiso('administrar_roles')->exito;
			
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
				
				case 'getPermList':
					$this->ListarPermisos()->ImprimirJson();
				break;
				
				default:
					$this->getInvalidError()->ImprimirJson();
				break;
			}
		}
		
		// Obtiene una lista de roles en formato json
		protected function Listar($filtros=null,$start=null,$limit = null,$sort = null) 
		{		
			$consulta = "SELECT * FROM rol WHERE 1";

			return parent::Listar($consulta, CACHE_ACTIVADO, $filtros, $start, $limit, $sort);
		}
		
		// Obtiene una lista de roles en formato json
		protected function ListarPermisos() 
		{		
			$consulta = "SELECT * FROM permiso ";		
			$sort     = isset($_REQUEST["sort"])?$_REQUEST["sort"]:"";
			return parent::Listar($consulta, false,null,null,null,$sort);
		}
		
		// todo: que hacemos si eliminamos un rol que ha creado registros
		protected static function Eliminar($idRol)
		{
			$bd = BD::Instancia();
			$consulta = "";
			$res = new Comunicacion();
	
			if($idRol == '')
			{	
				$res->exito   = false;
				$res->mensaje = t("Error deleting user");
				$res->errores = t("User not defined");
				return $res;
			}
	
			// si se elimina un administrador hay que asegurarse de que no es el único, sino nadie más podría administrar
			$consulta = "SELECT COUNT(*) FROM rol WHERE rol_id!= '". $idRol ."' AND rol_rol_id = '1' ";
	
			$numFilas = $bd->ContarFilas($consulta); 
			if (  $numFilas == 0 )
			{
				$res->exito = false;
				$res->mensaje = t("Error deleting user");
				$res->errores = t("This is the only administrator account. You can't delete it");
				return $res;
			}
			
			$consulta = "DELETE FROM rol WHERE rol_id='". $idRol ."'";
			$bd->Ejecutar($consulta);
			
			// si todo ha ido bien construimos la respuesta JSON y la devolvemos
			if( $bd->ObtenerErrores() == "" && Documento::EliminarPermisoRol($idRol) )
			{				
				$res->exito   = true;
				$res->mensaje = t("User deleted successfully");
				$res->errores = "";
			}
			else
			{
				$res->exito   = false;
				$res->mensaje = t("Error deleting user");
				$res->errores = $bd->ObtenerErrores();
			}
			return $res;
		}
	}
?>
