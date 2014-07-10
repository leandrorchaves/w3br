<?php
/**
 * Gerenciamento de Usuários
 *
 * @author Leandro Chaves
 */
class InstalarController {
    function iniciar($url){
        try{
            $user = Doctrine_Query::create()
                        ->from( 'Usuarios i' );
            $user->count();
            echo 'Sistema já está instalado.';
        }catch (Exception $e){
                Doctrine::createTablesFromModels();
                $usuario = new Usuarios();
                $usuario->login = 'admin';
                $usuario->senha = hash('whirlpool','admin');
                $usuario->nome = "Administrador";
                $usuario->save();
                echo 'Sistema Instalado com sucesso.';
        }
    }
}
?>
