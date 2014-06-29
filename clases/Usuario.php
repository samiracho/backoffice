<?php	
	class Usuario extends Restful
	{
		protected $rol;					    // rol asignado al usuario
		
		function Usuario($idUsuario = '')
		{
			$this->id     = "usuario_id";
			$this->tabla  = "usuario";
			
			$this->exitoListar     = t("User list obtained successfully");
			$this->errorListar     = t("Error obtaining user list");
			$this->exitoInsertar   = t("User created successfully");
			$this->exitoActualizar = t("User updated successfully");
			$this->errorInsertar   = t("Error creating user");
			$this->errorActualizar = t("Error updating user");	
			
			$this->campos = array(
				'usuario_id'        => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid user ID'),'valor'=>'','lectura'=>false),
				'usuario_nombre'    => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid user name'),'valor'=>'','lectura'=>false),
				'usuario_login'     => array('tipo'=>'string','nulo'=>false,'msg'=>t('Invalid user login'),'valor'=>null,'lectura'=>false),
				'usuario_rol_id'    => array('tipo'=>'int','nulo'=>false,'msg'=>t('Invalid Rol'),'valor'=>null,'lectura'=>false),
				'usuario_email'     => array('tipo'=>'email','nulo'=>true,'msg'=>t('Invalid email'),'valor'=>'','lectura'=>false),
				'usuario_apellidos' => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid surname1'),'valor'=>'','lectura'=>false),
				'usuario_detalles'  => array('tipo'=>'html','nulo'=>true,'msg'=>t('Invalid details'),'valor'=>'','lectura'=>false),
				'usuario_password'  => array('tipo'=>'string','nulo'=>false,'msg'=>t('Contraseña obligatoria'),'valor'=>null,'lectura'=>false),
				//'usuario_password2' => array('tipo'=>'string','nulo'=>false,'msg'=>t('Contraseña obligatoria'),'valor'=>null,'lectura'=>true),
				'usuario_enviarmail'=> array('tipo'=>'checkbox','nulo'=>true,'msg'=>t(''),'valor'=>'','lectura'=>true)
			);

			$this->relaciones = array(
			
				'rol' => array (
					'tabla'         => 'rol',
					'relacion'      => '1a1',
					'soloLectura'   => true,
					'clavePrimaria' => 'usuario_rol_id',
					'claveAjena1'   => 'rol_id',
					'claveAjena2'   => '',
					'campos'        => array(
						'rol_id'        => array('tipo'=>'int','nulo'=>true,'msg'=>t('Invalid rol id'),'valor'=>'','lectura'=>true),					
						'rol_nombre'    => array('tipo'=>'string','nulo'=>true,'msg'=>t('Invalid rol name'),'valor'=>'','lectura'=>true)
					)
				)
			);
			
			$this->campos['usuario_id']['valor'] = $idUsuario;
		}

		protected function runAction($action, $params)
		{
			$adminDocs = Usuario::TienePermiso('administrar_usuarios')->exito;
			
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
				
				case 'checkUnique':
					$idUsuario = isset($_REQUEST["idUsuario"])?$_REQUEST["idUsuario"]:'';
					$login     = isset($_REQUEST["login"])?$_REQUEST["login"]:"";				
					$res = $this->ComprobarLoginUnico($login,$idUsuario);
					echo $res->exito ? 1 : 0;					
				break;
				
				case 'checkPassword':		
					Usuario::Login()->ImprimirJson();
				break;
				
				case 'logout':		
					if(session_id() !='')session_destroy();
					header( 'Location: index.php' );
				break;
					
				case 'checkLogin':
					$this->checkLogin()->ImprimirJson();	
				break;
				
				case 'checkSession':
					$this->checkSession();	
				break;
				
				case 'saveState':
					$datos      = isset($_REQUEST["data"])?$_REQUEST["data"]:"";
					Usuario::GuardarEstado($datos)->ImprimirJson();
				break;
				
				case 'readState':
					print_r(Usuario::ObtenerEstado());
				break;
				
				default:
					$this->getInvalidError()->ImprimirJson();
				break;
			}
		}
		
		protected function checkSession()
		{
			if(Usuario::EstaIdentificado())
			{	
				// enviamos el resultado en JSON
				echo "{success: true}";
			} 
			else 
			{
				// en caso contrario enviamos un error para informar
				echo "{success: false, errors: { reason: 'Sesión caducada.' }}";
			}	
		}
		
		protected function checkLogin()
		{
			$res = new Comunicacion();
			$res->exito   = true;
			$res->mensaje = "User login check";
			$res->errores = "";
			
			if ( Usuario::EstaIdentificado() ){
				  $res->datos = array('logged' => true  );
			}else $res->datos = array('logged' => false );
			
			return $res;
		}
		
		// Obtiene una lista de usuarios en formato json
		protected function Listar($filtros=null,$start=null,$limit = null,$sort = null) 
		{		
			$consulta = "SELECT usuario_id,usuario_nombre,usuario_login,usuario_rol_id, usuario_email, usuario_apellidos rol_nombre 
			             FROM usuario U INNER JOIN rol ON usuario_rol_id=rol_id WHERE 1";

			return parent::Listar($consulta, CACHE_ACTIVADO, $filtros, $start, $limit, $sort);
		}
		
		protected static function GuardarEstado($datos)
		{
			$bd = BD::Instancia();
			$consulta = "";
			$res = new Comunicacion();
			$idUsuario = Usuario::IdUsuario();
			$datosGuardados;
			$datosLeidos;
				
			
			if($idUsuario != 0)
			{		
				$datosGuardados    = Comunicacion::DecodificarJson(Usuario::ObtenerEstado());
				$datosLeidos       = Comunicacion::DecodificarJson($datos);		
				$temp              = array();			
				$tamDatosGuardados = is_array($datosGuardados) ? sizeof($datosGuardados) : 0;
				$tamDatosLeidos    = is_array($datosLeidos) ? sizeof($datosLeidos) : 0;
				
				if($tamDatosGuardados != 0)
				{
					if($tamDatosLeidos == 0) $datosLeidos = $datosGuardados;
					else
					{
						for($i = 0; $i < $tamDatosGuardados ; $i++)
						{
							$encontrado = false;
							
							for($j = 0; $j < $tamDatosLeidos; $j++)
							{
								if( $datosGuardados[$i]->name == $datosLeidos[$j]->name)
								{
									$encontrado = true;
									break;
								}
							}
							
							if(!$encontrado)array_push($temp,$datosGuardados[$i]);
						}

						if(sizeof($temp)>0)
						{
							$datosLeidos = array_merge($datosLeidos,$temp);
						}
					}
				}	

				$bd->Ejecutar("UPDATE usuario SET usuario_estado='".serialize($datosLeidos)."' WHERE usuario_id='".$idUsuario."' ");
				if( $bd->ObtenerErrores() == "" )
				{				
					$res->exito   = true;
					$res->mensaje = t("User state updated successfully");
					$res->errores = "";
				}
				else
				{
					$res->exito   = false;
					$res->mensaje = t("Error updating user state");
					$res->errores = $bd->ObtenerErrores();
				}
			}
			else
			{
				$res->exito   = false;
				$res->mensaje = t("Error updating user state. User not logged in");
				$res->errores = $bd->ObtenerErrores();
			}
			return $res;
		}
		
		protected static function ObtenerEstado()
		{
			$bd = BD::Instancia();
			$idUsuario = Usuario::IdUsuario();
			if($idUsuario != 0 && GUARDAR_ESTADO_PANELES)
			{
				$consulta = "SELECT usuario_estado FROM usuario WHERE usuario_id = '" .$idUsuario."'";
				$datos    = $bd->Ejecutar($consulta);	
				$fila     = $bd->ObtenerFila($datos);
				
				return json_encode(unserialize($fila['usuario_estado']));

			}
			else
			{
				return "''";
			}
		}
		
		public function Guardar()
		{
			$bd = BD::Instancia();
			$consulta = "";
			$res = new Comunicacion();
					
			// leemos los datos json
			parent::Leer();
			
			// la cuenta admin siempre será administrador así que no permitimos cambiar su rol
			if($this->campos['usuario_id']['valor'] == 1 && $this->campos['usuario_rol_id']['valor']!=1)
			{		
				$res->exito = false;
				$res->errores = t("You can't change admin accout rol");
				$res->mensaje = t("Error");
				return $res;
			}
			
			// comprobamos que el login no esté repetido
			$res = $this->ComprobarLoginUnico($this->campos['usuario_login']['valor'],$this->campos['usuario_id']['valor']);
			if(!$res->exito)return $res;	
			
			
			// ciframos el password
			$passOriginal = $this->campos['usuario_password']['valor'];
			$this->campos['usuario_password']['valor'] = $this->campos['usuario_password']['valor']!= "" ? $this->campos['usuario_password']['valor'] : '';
			
			// una vez hechas todas las comprobaciones intentamos guardar (él solo se encargará de decidir si es un insert o un update)
			$res = parent::Guardar(true,false);
			
			// Si todo ha ido bien enviamos un mail de confirmación al usuario
			if($res->exito){

                if($this->campos['usuario_enviarmail']['valor'] == 1)
                {
                    require_once 'Swift-5.0.1/lib/swift_required.php';
                    
                    $transport = Swift_SmtpTransport::newInstance(EMAIL_SMTP, EMAIL_PUERTO)
                    ->setUsername(EMAIL_USUARIO)
                    ->setPassword(EMAIL_PASS);
                    
                    $mailer = Swift_Mailer::newInstance($transport);

                    $doc = new DOMDocument();
                    $doc->load( dirname(__FILE__).'/../archivos/plantillas/correo.xml' );
                    
                    $asunto = $doc->getElementsByTagName( "asunto" )->item(0)->nodeValue;
					$asunto = str_replace('{tituloweb}', TITULO_WEB , $asunto);
					
                    $cuerpo = $doc->getElementsByTagName( "cuerpo" )->item(0)->nodeValue;		
                    $cuerpo = str_replace('{usuario}', $this->campos['usuario_login']['valor'], $cuerpo);
                    $cuerpo = str_replace('{contrasenya}', $passOriginal , $cuerpo);
					$cuerpo = str_replace('{urlbackoffice}', URL_BACKOFFICE, $cuerpo);
                    $cuerpo = str_replace('{tituloweb}', TITULO_WEB , $cuerpo);

                    $message = Swift_Message::newInstance($asunto)
                                ->setFrom( array( EMAIL_DIRECCION ) )
                                ->setTo( array( $this->campos['usuario_email']['valor'] ) )
                                ->addPart( $cuerpo ,'text/html')
                                ->setReturnPath(EMAIL_DIRECCION);
                    
                    $resultadoEnvio = $mailer->send($message);

                    if($resultadoEnvio == 0){
                        $res->exito = false;
                        $res->errores = "Fallo enviando e-mail de confirmación. <br />Si el problema persiste consulte con su administrador.";
                        $res->mensaje = t("Error");
                    }
                }		
			}
			
			return $res;
		}
		
		// todo: que hacemos si eliminamos un usuario que ha creado registros
		protected function Eliminar()
		{
			$bd = BD::Instancia();
			$consulta = "";
			$res = new Comunicacion();
	
			parent::Leer();
			
			$idUsuario = intval($this->campos['usuario_id']['valor']);
	
			if( empty($idUsuario) )
			{	
				$res->exito   = false;
				$res->mensaje = t("Error deleting user");
				$res->errores = t("User not defined or invalid ID");
				return $res;
			}
	
			// si se elimina un administrador hay que asegurarse de que no es el único, sino nadie más podría administrar
			$consulta = "SELECT COUNT(*) FROM usuario WHERE usuario_id!= '". $idUsuario ."' AND usuario_rol_id = '1' ";
	
			$numFilas = $bd->ContarFilas($consulta); 
			if (  $numFilas == 0 )
			{
				$res->exito = false;
				$res->mensaje = t("Error deleting user");
				$res->errores = t("This is the only administrator account. You can't delete it");
				return $res;
			}
			
			// no permitimos eliminar la cuenta de admin
			if($idUsuario == 1)
			{
				$res->exito = false;
				$res->mensaje = t("Error deleting user");
				$res->errores = t("You can't delete admin account");
				return $res;
			}
			
			$consulta = "DELETE FROM usuario WHERE usuario_id='". $idUsuario ."'";
			$bd->Ejecutar($consulta);
			
			// los registros que eran propiedad de este usuario ahora pasan a ser del administrador
			$bd->Ejecutar("UPDATE documento SET documento_usuario_id='1' WHERE documento_usuario_id='".$idUsuario."' ");
			$bd->Ejecutar("UPDATE noticia SET noticia_usuario_id='1' WHERE noticia_usuario_id='".$idUsuario."' ");
			
			if( $bd->ObtenerErrores() == '' && Documento::EliminarPermiso($idUsuario))
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
		
		// comprueba que el login sea unico
		protected function ComprobarLoginUnico($login, $idUsuario)
		{
			return ObjetoBD::ComprobarUnico("usuario_login", $login, $this->tabla,$this->id,$idUsuario);			
		}
			
		// --------------------------------------------------------------------------------------------------------------------------------
		// Funciones para hacer login, comprobar permisos e.t.c
		// --------------------------------------------------------------------------------------------------------------------------------
		
		// funcion para hacer login
		public static function Login() 
		{
			
			$login    = isset($_POST["loginUsername"]) ? $_POST["loginUsername"] : "";
			$password = isset($_POST["loginPassword"]) ? $_POST["loginPassword"] : "";
			$identificador = array();	
			$bd = BD::Instancia();
			$login  = mysql_real_escape_string($login);
			$mensaje = new Comunicacion();
			//$password = md5($password);
			$consulta = "SELECT usuario_id FROM usuario WHERE usuario_login = '" . $login . "' AND usuario_password = '" . $password . "' LIMIT 1";	
			
			$datos    = $bd->Ejecutar($consulta);
			
			if( $bd->ObtenerErrores() == "" )
			{
				$fila = $bd->ObtenerFila($datos);
				if ($fila['usuario_id']!='')
				{
					$identificador["usuario_id"] = $fila['usuario_id']; 
					$identificador["hash"]       = md5($fila['usuario_id'].BACKOFFICE_CLAVE);
					$identificador["ttl"]        = time();
					if(session_id() == '' ) session_start();
					$_SESSION["usuario"]         = $identificador;
					$mensaje->exito  = true;				
				}
				else {
					$mensaje->exito  = false;
					$mensaje->mensaje = t('Usuario o Password inválido');
				}
			}
			else
			{
				$mensaje->mensaje = t('BD Error');
				$mensaje->exito   = false;
				$mensaje->errores = $bd->ObtenerErrores();
			}
			return $mensaje;
		}
		
		public static function EstaIdentificado()
		{
			if(session_id() == "") 
			{ 
				return false;
			} 
			else 
			{ 
				if( !isset($_SESSION["usuario"]) || sizeof($_SESSION["usuario"])!=3 || ((time() - $_SESSION["usuario"]["ttl"]) > TTL_SESION)  ){		 
					return false;
				}
				else{
					$_SESSION["usuario"]["ttl"] = time();
					return true;
				}
			} 	
		}

		// para comprobar si el usuario tiene un permiso en concreto
		public static function TienePermiso($permiso)
		{
			$mensaje         = new Comunicacion();
			$mensaje->exito  = true;
			
			if(!Usuario::EstaIdentificado()){
				$mensaje->mensaje = t('You dont have the required permissions');
				$mensaje->exito   = false;
				$mensaje->errores = t('SESSION_ERROR');	
				return $mensaje;
			}
			
			$identificador = $_SESSION["usuario"];
			
			
			// si no coincide el hash no damos permisos
			if( md5($identificador["usuario_id"].BACKOFFICE_CLAVE) != $identificador["hash"] )
			{
				$mensaje->mensaje = t('You dont have the required permissions');
				$mensaje->exito   = false;
				$mensaje->errores = t('SESSION_ERROR');	
				return $mensaje;
			}
			else
			{
				$bd = BD::Instancia();
	
				// si tiene el rol de administrador siempre tiene permiso
				$consulta = "SELECT COUNT(*) FROM usuario WHERE usuario_id= '". intval($identificador["usuario_id"]) ."' AND usuario_rol_id = '1' ";
				if ( $bd->ContarFilas($consulta) > 0 )
				{
					return $mensaje;
				}
				
				$consulta = "SELECT COUNT(*) FROM usuario LEFT JOIN rol ON usuario_rol_id = rol_id WHERE usuario_id = '". intval($identificador["usuario_id"]) ."' 
				             AND FIND_IN_SET((SELECT permiso_id FROM permiso WHERE permiso_nombreinterno ='".$permiso."' ),rol_permisos)";
				
				if ( $bd->ContarFilas($consulta) > 0 )
				{
					return $mensaje;
				}
				return false;
			}
		}
		
		// Con esta función se obtiene una lista de variables en javascript con los permisos del usuario.
		// No es inseguro porque todas las operaciones se comprueban del lado del servidor.
		// Lo he hecho así para por ejemplo poder ocultar botones de la interfaz a un usuario sin permisos e.t.c
		// De este modo también evito escribir variables php en los archivos .js, porque sino el navegador no podría guardarlos en la caché.
		public static function ObtenerPermisosJS()
		{
			$bd = BD::Instancia();

			$consulta = "SELECT permiso_nombreinterno FROM permiso";
			$datos = $bd->Ejecutar($consulta);
			
			if( $bd->ObtenerErrores() == "" )
			{
				while($fila = $bd->ObtenerFila($datos))
				{
					$tienePermiso = Usuario::TienePermiso($fila['permiso_nombreinterno'])->exito ? 'true' : 'false';
					echo 'CONFIG.'.$fila['permiso_nombreinterno'].'= '.$tienePermiso.';';
					echo chr(13).chr(10);
				}
			}
			else
			{
				echo 'CONFIG.perms = null;';
			}
		}
		
		// devuelve la id del usuario que ha iniciado sesión, si la variable de sesión ha sido modificada devolverá 0
		public static function IdUsuario()
		{
			if(!Usuario::EstaIdentificado()) return 0;
			
			$identificador = $_SESSION["usuario"];
			
			if( md5($identificador["usuario_id"].BACKOFFICE_CLAVE) != $identificador["hash"] )
			{
				return 0;
			}
			else return $identificador["usuario_id"];
		}
	}
?>
