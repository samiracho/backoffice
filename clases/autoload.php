<?php

// DATOS DE CONFIGURACI�N
require '../config.php';

// AUTOLOAD DE CLASES
function mi_autocargador($clase) {
    include $clase . '.php';
}

spl_autoload_register('mi_autocargador');

?>