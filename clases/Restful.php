<?php

	class Restful extends ObjetoBD
	{			
		// Función que llama al método runAction del controlador para ejecutar la acción
		public static function run($action = null, $controller = null, $params = null)
		{
			$invalidController = new Comunicacion();
			$invalidController->exito   = false;
			$invalidController->mensaje = t('Invalid request');
			
			$myAction     = $action!=null ? $action : (isset($_REQUEST["action"]) ? $_REQUEST["action"] : null);
			$myController = $controller!=null ? $controller : (isset($_REQUEST["controller"]) ? $_REQUEST["controller"] : null);
			$myParams     = $params!=null ? $params : (isset($_REQUEST["params"]) ? $_REQUEST["params"] : null);
			
			if( $myAction && $myController )
			{
				$controller_name = ucfirst($myController);
				if (class_exists($controller_name)) {
					$controller = new $controller_name();
					$controller->runAction($myAction, $myParams);
				} 
				else{
					$invalidController->errores = t('Invalid controller: ').$controller_name;
					$invalidController->ImprimirJson();
				}
			
			} else return;
		}
		
		// Este método debe ser implementado por todas las subclases para hacer públicas las acciones que pueden realizar
		protected function runAction($action, $params){
			return;
		}
		
		protected function getPermError()
		{
			$permError = new Comunicacion();
			$permError->exito   = false;
			$permError->mensaje = t('You dont have the required permissions');
			$permError->errores = 'SESSION_ERROR';
			return $permError;		
		}
		
		protected function getDBError()
		{
			$permError = new Comunicacion();
			$permError->exito   = false;
			$permError->mensaje = t('Error connecting to database');
			$permError->errores = 'DB_ERROR';
			return $permError;		
		}
		
		protected function getInvalidError()
		{
			$invalidError = new Comunicacion();
			$invalidError->exito   = false;
			$invalidError->mensaje = t('Invalid action');
			$invalidError->errores = t('Invalid action');
			return $invalidError;		
		}
		
		private function getPathInfo()
		{
			if (isset($_SERVER["PATH_INFO"])){
				$cai = '/^\/([a-z]+\w)\/([a-z]+\w)\/([0-9]+)$/';  // /controller/action/id
				$ca =  '/^\/([a-z]+\w)\/([a-z]+)$/';              // /controller/action
				$ci = '/^\/([a-z]+\w)\/([0-9]+)$/';               // /controller/id
				$c =  '/^\/([a-z]+\w)$/';                         // /controller
				$i =  '/^\/([0-9]+)$/';                           // /id
				$matches = array();
				if (preg_match($cai, $_SERVER["PATH_INFO"], $matches)) {
					$controller = $matches[1];
					$action = $matches[2];
					$id = $matches[3];
				} else if (preg_match($ca, $_SERVER["PATH_INFO"], $matches)) {
					$controller = $matches[1];
					$action = $matches[2];
				} else if (preg_match($ci, $_SERVER["PATH_INFO"], $matches)) {
					$controller = $matches[1];
					$id = $matches[2];
				} else if (preg_match($c, $_SERVER["PATH_INFO"], $matches)) {
					$controller = $matches[1];
				} else if (preg_match($i, $_SERVER["PATH_INFO"], $matches)) {
					$id = $matches[1];
				}
			}	
		}		
		
		protected function read($permiso = null)
		{		
			if( $permiso || Usuario::EstaIdentificado() ){
				$filtros        = isset($_REQUEST["filtros"])?$_REQUEST["filtros"]:"";
				$limit          = isset($_REQUEST["limit"])?$_REQUEST["limit"]:SELECT_LIMIT;
				$start          = isset($_REQUEST["start"])?$_REQUEST["start"]:0;
				$sort           = isset($_REQUEST["sort"])?$_REQUEST["sort"]:"";
				$this->Listar($filtros,$start,$limit,$sort)->ImprimirJson();
			} else $this->getPermError()->ImprimirJson();
		}
		
		protected function add($permiso = null)
		{
			if ($permiso) $this->Guardar()->ImprimirJson();
			else $this->getPermError()->ImprimirJson();
		}
		
		protected function destroy($permiso = null)
		{
			if ($permiso)$this->EliminarDato()->ImprimirJson();
			else $this->getPermError()->ImprimirJson();			
		}
	}
?>