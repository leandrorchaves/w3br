<?php
/**
 * Monta o container de apresentação da popup.
 * Incluindo cabeçalho e rodapé.
 * @author Leandro Chaves
 * @version 2009.12.01
 */
class PopupController {
    private $titulo;
    private $config;
    public $smarty;
    Function PopupController(){
        require('www/add/smarty/Smarty.class.php');
		$this->config = new Config;
        $this->titulo = "";
		$this->smarty = new Smarty;
    }
    /**
     * Monta um topo personalizado para as páginas popup
     */
    function topo(){
            $this->smarty -> assign('titulo',$this->titulo);
            $this->smarty -> display('container/popup_topo.html');
    }
    /**
     * Monta um rodapé personalizado para as páginas popup
     */
    function rodape(){
            $this->smarty = new Smarty;
            $this->smarty -> display('container/popup_rodape.html');
    }
}
?>
