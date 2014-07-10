<?php 
function __autoload($class) {
    W3br::loadClass($class);
}
spl_autoload_register('__autoload');
?>
