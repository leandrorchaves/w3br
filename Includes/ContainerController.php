<?php
/**
 * Monta o container de apresentação da página.
 * Incluindo cabeçalho, menu e rodapé.
 * @author Leandro Chaves
 * @link http://leandrochaves.com
 */
class ContainerController{
    private $title;
    public $smarty;
    /**
     * Contrutor - Adiciona e configura o Smarty templates.
     */
    function  __construct(){
        require(W3BR_PATH.'/Plugins/Smarty/Smarty.class.php');
        $this->smarty = new Smarty;
        $this->smarty->template_dir = SMARTY_TEMPLATES.TEMPLATE;
        $this->smarty->compile_dir = SMARTY_COMPILE;
        $this->smarty->config_dir = SMARTY_CONFIG;
        $this->smarty->cache_dir = SMARTY_CACHE;

        $this->title = TITLE;
    }
    /**
     * Define o titulo da página.
     * @param String $titulo
     */
    function title($titulo){
        if($titulo != "") $this->title = $titulo." - ".$this->title;
    }
    /**
     * Monta o topo da página
     */
    function header(){
        if(isset($_SESSION['usuarioid']))$this->smarty->assign('usuario',$_SESSION['usuario']);
        $this->smarty -> assign('titulo',$this->title);
        $this->smarty -> display('container/header.html');
}
     /**
      * Monta o rodapé da página
      */
    function footer(){
        $this->smarty -> display('container/footer.html');
    }
}
?>