<?php

class UsuarioController {

    private function menu() {
        $menus = Doctrine_Query::create()
                // Nível I
                ->from('Model_AclMenu m')
                ->leftjoin('m.AclPermissaoMenu pm')
                ->leftjoin('pm.AclPermissao pmp')
                ->leftjoin('pmp.AclPapelPermissao mpp')
                ->leftjoin('mpp.AclPapel mp')
                ->leftjoin('mp.Usuarios mu')
                // Nível II
                ->leftjoin('m.AclMenu f')
                ->leftjoin('f.AclPermissaoMenu fpm')
                ->leftjoin('fpm.AclPermissao fpmp')
                ->leftjoin('fpmp.AclPapelPermissao fpp')
                ->leftjoin('fpp.AclPapel fp')
                ->leftjoin('fp.Usuarios fu')
                // Nível III
//                ->leftjoin('f.AclMenu n')
                ->where("m.pai IS NULL")
                ->andWhere("mu.id = :user OR fu.id = :user", Array(':user' => $_SESSION[APPLICATIONID]['usuarioid']))
                ->orderBy("m.ordem")
                ->addOrderBy("f.texto")
                ->execute(Array(), Doctrine::HYDRATE_ARRAY);
//        return $menus;
        return $this->menuAjustes($menus);
//        return Array(Array('texto'=>"Usuários",'icone'=>"h2a/core/icons/folder.png",
//            'filhos'=>Array(Array('texto'=>"Buscar",'icone'=>"h2a/core/icons/folder_magnify.png",'janela'=>"h2a.core.usuario.Listar"))));
    }

    private function menuAjustes($menus) {
        for ($x = 0; $x < sizeof($menus); $x++) {
            if (isset($menus[$x]['AclMenu']) && 0 < sizeof($menus[$x]['AclMenu'])) {
                $menus[$x]['filhos'] = $this->menuAjustes($menus[$x]['AclMenu']);
            }
        }
        return $menus;
    }

    public function addAction() {
        $post = $_POST;

        // Válida o campo login
        if (!isset($post['login']) || 0 > strlen($post['login'])) {
            throw new Exception('O campo login deve ser preenchido!');
        }
        // Válida o campo nome
        if (!isset($post['nome']) || 0 > strlen($post['nome'])) {
            throw new Exception('O campo nome deve ser preenchido!');
        }
        // Válida o campo confirmação de senha
        if ($post['senha'] != $post['confirma']) {
            throw new Exception('A senha e a confirmação não conferem!');
        }

        if ($post['id'] == "0") {
            // Válida o campo senha
            if ($post['senha'] == "" || strlen($post['senha']) < 2) {
                throw new Exception('O campo senha deve ser preenchido!');
            }

            // Verifica se o usuário já está cadastrado
            $obj = Doctrine::getTable('Model_Usuarios')->findBy('login', $post['login']);
            if (sizeof($obj) > 0) {
                throw new Exception(" Usuário já cadastrado!");
            } else {
                $obj = new Model_Usuarios();
            }
        } else {
            //TENTA ATUALIZAR CADASTRO
            $obj = Doctrine_Core::getTable('Model_Usuarios')->find($post['id']);
        }
        if ($post['ativo'] === 'true') {
            $stats = true;
        } else {
            $stats = false;
        }
        $obj->login = $post['login'];
        $obj->nome = $post['nome'];
        $obj->email = (!in_array($post['email'], Array('', 'null', 'NULL')) ? $post['email'] : NULL);
        $obj->id_papel = $post['papel'];
        $obj->status = $stats;
        if ($post['senha'] != "" && strlen($post['senha']) > 2) {
            $obj->senha = sha1($post['senha']);
        }
//            $obj->receber_email = (boolean) $post['receber'];
        if ($post['receber'] === 'true') {
            $obj->receber_email = true;
        } else {
            $obj->receber_email = false;
        }
        $obj->save();

        // Atualiza o armazém que o usuário tem acesso
        Doctrine_Query::create()
                ->delete("Model_UsuarioArmazem")
                ->where("id_usuario = ?", $obj->id)
                ->execute();
        if (isset($post['armazem']) && $post['armazem'] !== '[]') {
            $armazens = json_decode($post['armazem']);
            $ids = Array();
            foreach ($armazens as $item) {
                if(!in_array($item->id, $ids)) {
                    $ids[] = $item->id;
                    $armazem = new Model_UsuarioArmazem();
                    $armazem->id_usuario = $obj->id;
                    $armazem->id_armazem = $item->id;
                    $armazem->email = (boolean) $post['receber'];
                    $armazem->save();
                }
            }
        }
        // Atualiza o prestador ao qual o usuário está vinculado
        Doctrine_Query::create()
                ->delete("Model_ManUsuarioPrestador")
                ->where("id_usuario = ?", $obj->id)
                ->execute();
        if (isset($post['prestador']) && $post['prestador'] != 0) {
            $prestador = new Model_ManUsuarioPrestador;
            $prestador->id_usuario = $obj->id;
            $prestador->id_prestador = $post['prestador'];
            $prestador->save();
        }
        $response = Response::getInstance();
        $response->sucesso = 1;
    }

    public function senhaAction() {
        $post = $_POST;
        $saida = Array();
        //TENTA ATUALIZAR CADASTRO
        $obj = Doctrine_Core::getTable('Model_Usuarios')->find($_SESSION[APPLICATIONID]['usuarioid']);
        try {
            if ($obj->senha != $post['atual']) {
                throw new Exception("Senha atual inválida!");
            }
            if ($post['nova'] != $post['confirma']) {
                throw new Exception("A nova senha e a confirmação não conferem!");
            }

            $obj->senha = $post['nova'];
            $obj->save();
            $saida['sucesso'] = 1;
            $saida['desc'] = "Senha alterada com sucesso.";
        } catch (Exception $e) {
            $saida['erro'] = "fall";
            $saida['desc'] = $e->getMessage();
            $saida['sucesso'] = 0;
        }
        $saida['sucesso'] = (isset($saida['sucesso']) ? $saida['sucesso'] : 1);
        echo json_encode($saida);
        die;
    }

    public function lerAction() {
        $post = json_decode(json_encode($_POST));
        try {
            if (isset($post->id)) {
                $obj = Doctrine_Query::create()
                                ->from('Model_Usuarios u')
                                ->leftJoin("u.ManUsuarioPrestador")
                                ->leftJoin("u.UsuarioArmazem ua")
                                ->leftJoin("ua.Armazem")
                                ->where("u.id = ?", $post->id)
                                ->execute()->getFirst();
                if ($obj) {
                    $obj->senha = "";
                    echo json_encode($obj->toArray());
                } else {
                    $obj = new Model_Usuarios();
                    echo json_encode($obj->toArray());
                }
            }
        } catch (Exception $e) {
            echo "erro" . $e->getMessage();
        }

        die;
    }

    public function retornoAction() {
        $post = json_decode(json_encode($_POST));

        $query = Doctrine_Query::create()
                ->select("u.id,u.login,u.nome,u.codigo,u.email,u.status,u.receber_email,ua.*,a.*,up.descricao papel")
                ->from('Model_Usuarios u')
                ->leftJoin('u.UsuarioArmazem ua')
                ->leftJoin('u.AclPapel up')
                ->leftJoin('ua.Armazem a');

//        if (!in_array($post->id, Array(null, 'null', ""))) {
//            $query->addWhere("id = ?", $post->id);
//        }
        if (!in_array($post->login, Array(null, 'null', ""))) {
            $query->addWhere("login LIKE ?", '%' . $post->login . '%');
        }
        if (!in_array($post->nome, Array(null, 'null', ""))) {
            $query->addWhere("nome LIKE ?", '%' . $post->nome . '%');
        }
        if (!in_array($post->status, Array(null, 'null', "", "todos"))) {
            $query->addWhere("status = ?", $post->status);
        }

        $pager = new Doctrine_Pager(
                $query, 1, 500
        );
        echo json_encode(new Response($pager));
        die;
    }

    public function papeisAction() {
        $query = Doctrine_Query::create()
                ->from('Model_AclPapel');
        $objs = $query->execute()->toArray();
        echo json_encode($objs);
        die;
    }

    public function armazensAction() {
        $query = Doctrine_Query::create()
                ->from('Model_Armazem');
        $objs = $query->execute()->toArray();
        echo json_encode($objs);
        die;
    }

    public function prestadoresAction() {
        $query = Doctrine_Query::create()
                ->from('Model_ManPrestador');
        $objs = $query->execute()->toArray();
        echo json_encode($objs);
        die;
    }

    public function cargosAction() {
//        $post = json_decode(json_encode($_POST));
        $query = Doctrine_Query::create()
                ->from('Model_ManCargo');
        $objs = $query->execute()->toArray();
        echo json_encode($objs);
        die;
    }

    public function euAction() {
        $response = new Response();
//        $post = json_decode(json_encode($_POST));
        if (isset($_SESSION[APPLICATIONID])) {
            $obj = Doctrine_Query::create()
                    ->from('Model_Usuarios u')
                    ->leftJoin("u.AclPapel p")
                    ->leftJoin("p.AclPapelPermissao pp")
                    ->leftJoin("u.UsuarioArmazem ua")
                    ->leftJoin("ua.Armazem a")
                    ->leftJoin("u.ManUsuarioPrestador up")
                    ->leftJoin("up.ManPrestador pr")
                    ->where("u.id = ?", $_SESSION[APPLICATIONID]['usuarioid'])
                    ->execute()
                    ->getFirst();
            if ($obj) {
                $obj->senha = "";
            } else {
                $obj = new Model_Usuarios();
            }
            $dados = $obj->toArray();
            $dados['menu'] = $this->menu();

            header('Content-Type: application/json');
            $response->sucesso = 1;
            $response->dados = $dados;
            $response->version = VERSION;
        } else {
            $response->login = 0;
        }
        echo json_encode($response);
    }

}
