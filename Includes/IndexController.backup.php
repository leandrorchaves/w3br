<?php
/**
 * @todo Excluir IndexController()
 */
class IndexController{
    public $prod;
    public $recentes;
    function display($url){
        if(!isset($_SESSION['usuario']))$_SESSION['usuario']=NULL;
        if($_SESSION['usuario']!=NULL){
            $_SESSION['url'] = array(0=>'',1=>'');
            //View
            W3br::loadClass('ContainerController');
            $container = new ContainerController;
            $container->header();
            $container->smarty->display('index/index.html');
            $container->footer();
        }
        else $this->login($url);
    }
    function login($url){
        //View
        W3br::loadClass('ContainerController');
        $container = new ContainerController;
        $container->header();
        $container->footer();
        unset ($_SESSION['saida']);
    }
}
?>
