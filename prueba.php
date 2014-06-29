<?
$path = substr( __FILE__, strlen( $_SERVER[ 'DOCUMENT_ROOT' ] ) );

print_r($path);

$url  = ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'].'/';
$search  = array( DIRECTORY_SEPARATOR.basename(__FILE__), '\\', '//' );
$replace = array('', '/', '/');
$path = str_replace($search, $replace, $path);
print_r($path);
$url.= $path;