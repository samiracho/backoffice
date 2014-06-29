<?php	
// clase para cachear resultados. En construcción
class Cache
{	
	public static function Guardar($prefijo, $nombre, $datos)
	{
		$datosSerializados = serialize($datos);
		$archivo = RUTA_CACHE.'cache_'.$prefijo.'_'.md5($nombre);
		
		if ( is_writable(RUTA_CACHE) ) file_put_contents($archivo, $datosSerializados);	
	}
	
	public static function Obtener($prefijo,$nombre)
	{
		$tiempo = 0;
		$datos = false;
		
		$archivo = RUTA_CACHE.'cache_'.$prefijo.'_'.md5($nombre);
		
		if (@file_exists($archivo) && is_readable($archivo) ) 
		{
			$tiempo = @filemtime($archivo);
		}
		else
		{
			return $datos;
		}
		
		if (time() - $tiempo < TTL_CACHE) 
		{
			$datos = unserialize( file_get_contents( $archivo ) );
		}
		else
		{
			Cache::Eliminar($prefijo,$nombre);
		}
		
		return $datos;
	}
	
	public static function Eliminar($prefijo,$nombre)
	{
		$archivo = RUTA_CACHE.'cache_'.$prefijo.'_'.md5($nombre);
		if (@file_exists($archivo) && is_writable($archivo)) 
		{
			unlink($archivo);
		}
	}
	
	public static function EliminarPorPrefijo($prefijo)
	{
		foreach ( glob(RUTA_CACHE.'cache_'.$prefijo.'_*') as $nombre_archivo) 
		{
			if (@file_exists($nombre_archivo) && is_writable($nombre_archivo)) 
			{
				unlink($nombre_archivo);
			}
		}	
	}
}
?>