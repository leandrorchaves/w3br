<?php
/**
 * Modelo de Gerenciador de Arquivos
 *
 * @author Leandro Chaves <leandrorchaves@gmail.com>
 * @link http://leandrorchaves.com
 */
class _fileController{
    protected $dir;
    function  __construct() {
        Framework::loadClass('LoginController');
        Framework::loadClass('ContainerController');
    }
    /**
     * insert a new file
     * @param Array $url
     */
    function insert($url){
        // upload a new file
        $img = $_FILES['src']['name'];
        $dir = $this->dir."/".$img;
        if(move_uploaded_file($_FILES['src']['tmp_name'], $dir)){
            echo '<div id="output">0</div>';
            echo '<div id="message">'.$_FILES['src']['name'].'</div>';
        }else{
            echo '<div id="output">failed</div>';
            echo '<div id="message">'.$_FILES['src']['error'].'</div>';
        }

    }
}
?>