<?php

class AclController {

    /**
     * Lista a actions cadastradas. 
     */
    public function actionsAction() {
        $post = json_decode(json_encode($_POST));

        $query = Doctrine_Query::create()
//                ->select("*")
                ->from('Model_AclAction a')
        ;

        if (isset($post->id) && "" != $post->id) {
            $query->addWhere("a.id  = :id", Array(
                ':id' => $post->id
            ));
        }

        // Url
        if (isset($post->url) && "" != $post->url) {
            $query->addWhere("a.nome LIKE :url", Array(
                ':url' => "%{$post->url}%"
            ));
        }

        // Ordenação
        $query->orderBy("a.nome ASC");
        // Cria o paginador
        if (isset($post->paginaatual)) {
            $pager = new Doctrine_Pager(
                    $query, $post->paginaatual, // Current page of request
                    $post->registros// (Optional) Number of results per page. Default is 25
            );

            $objs = Array();
            $objs["dados"] = $pager->execute()->toArray();

            $objs["lastPage"] = $pager->getLastPage();
        } else {
            $objs["dados"] = $query->execute()->toArray();
        }
        echo json_encode($objs);
        die;
    }

    /**
     * Remove os menus especificados.
     */
    public function removermenuAction() {
        $post = $_POST;
        $dados = json_decode($post["dados"]);
        $response = new Response();
        try {
            if (0 < sizeof($dados)) {
                // Exclui o vinculo com as permissões
                Doctrine_Query::create()
                        ->delete("Model_AclPermissaoMenu")
                        ->whereIn("id_menu", $dados)
                        ->execute();

                // Exclui as actions
                Doctrine_Query::create()
                        ->delete("Model_AclMenu")
                        ->whereIn("id", $dados)
                        ->execute();
            }

            $response->sucesso = 1;
        } catch (Exception $e) {
            $response->sucesso = 0;
            $response->trace = $e->getTraceAsString();
            $response->erro = $e->getMessage();
        }
        $post["sucesso"] = (isset($post["sucesso"]) ? $post["sucesso"] : 1);
        $post["dados"] = $dados;
        echo json_encode($post);
        die;
    }

    /**
     * Remove as actions especificadas.
     */
    public function removeractionAction() {
        $post = $_POST;
        $dados = json_decode($post["dados"]);
        try {
            // Exclui o vinculo com as permissões
            if (0 < sizeof($dados)) {
                Doctrine_Query::create()
                        ->delete("Model_AclPermissaoAction")
                        ->whereIn("id_action", $dados)
                        ->execute();
                // Exclui as actions
                Doctrine_Query::create()
                        ->delete("Model_AclAction")
                        ->whereIn("id", $dados)
                        ->execute();
            }


            $post["sucesso"] = 1;
        } catch (Exception $e) {
            $post["sucesso"] = 0;
            $post["desc"] = "Ocorreu um erro. Tente novamente!";
            $post["trace"] = $e->getTraceAsString();
            $post["erro"] = "fall";
        }
        $post["sucesso"] = (isset($post["sucesso"]) ? $post["sucesso"] : 1);
        $post["dados"] = $dados;
        echo json_encode($post);
        die;
    }

    /**
     * Remove as permissões especificadas.
     */
    public function removerpermissaoAction() {
        $post = $_POST;
        $dados = json_decode($post["dados"]);
        try {
            // Exclui o vinculo com as actions
            Doctrine_Query::create()
                    ->delete("Model_AclPermissaoAction")
                    ->whereIn("id_permissao", $dados)
                    ->execute();

            // Exclui o vinculo com os papéis
            Doctrine_Query::create()
                    ->delete("Model_AclPapelPermissao")
                    ->whereIn("id_permissao", $dados)
                    ->execute();

            // Exclui as permissões
            Doctrine_Query::create()
                    ->delete("Model_AclPermissao")
                    ->whereIn("id", $dados)
                    ->execute();

            $post["sucesso"] = 1;
        } catch (Exception $e) {
            $post["sucesso"] = 0;
            $post["desc"] = "Ocorreu um erro. Tente novamente!";
            $post["trace"] = $e->getTraceAsString();
            $post["erro"] = "fall";
        }
        $post["sucesso"] = (isset($post["sucesso"]) ? $post["sucesso"] : 1);
        $post["dados"] = $dados;
        echo json_encode($post);
        die;
    }

    /**
     * Remove os papéis especificados.
     */
    public function removerpapelAction() {
        $post = $_POST;
        $dados = json_decode($post["dados"]);
        if (0 < count($dados)) {
            // Verifica se existe usuário vinculado ao papel
            $existe = Doctrine_Query::create()
                    ->from("Model_Usuarios")
                    ->whereIn("id_papel", $dados)
                    ->count();
            if(0 < $existe){
                throw new Exception('Não é possível excluir papel que possua usuário vinculado!');
            }
            
            // Exclui o vinculo com as permissões
            Doctrine_Query::create()
                    ->delete("Model_AclPapelPermissao")
                    ->whereIn("id_papel", $dados)
                    ->execute();

            // Exclui as permissões
            Doctrine_Query::create()
                    ->delete("Model_AclPapel")
                    ->whereIn("id", $dados)
                    ->execute();
        }
    }

    public function exportarAction() {
        $query = Doctrine_Query::create()
                ->from('Model_AclPermissao p')
                ->leftJoin('p.AclPermissaoAction pa')
                ->leftJoin('pa.AclAction a')
                ->leftJoin('p.AclPermissaoMenu pm')
                ->leftJoin('pm.AclMenu m')
                ->leftJoin('m.Pai mp')
        ;
        $hora = date('Ymd_His');
        header('Content-type: text/json');
        header("Content-Disposition: attachment; filename=\"acl_{$hora}.json\"");
        echo json_encode($query->execute(Array(), Doctrine::HYDRATE_ARRAY));
    }

    public function importarAction() {
        $response = new Response();
        try {
            foreach ($_FILES as $file) {
                $path = $file['tmp_name'];
                if (file_exists($path)) {
                    $arquivo = file_get_contents($path);
                    $permissoes = json_decode($arquivo);
                    // Percorre as permissões
                    foreach ($permissoes as $permissao) {
                        if (!in_array($permissao->descricao, Array(null, 'null', ''))) {
                            // Verifica se a permissão já existe
                            $oPermissao = Doctrine_Query::create()
                                    ->from('Model_AclPermissao')
                                    ->where('descricao = ?', $permissao->descricao)
                                    ->fetchOne();
                            if (!$oPermissao) {
                                $oPermissao = new Model_AclPermissao();
                                $oPermissao->descricao = $permissao->descricao;
                                $oPermissao->save();
                            }


                            // Percorre as actions
                            foreach ($permissao->AclPermissaoAction as $permissao_action) {
                                // verifica se a action já existe
                                $action = $permissao_action->AclAction;
                                $oAction = Doctrine::getTable('Model_AclAction')->findOneBy('nome', $action->nome);
                                if (!$oAction) {
                                    $oAction = new Model_AclAction();
                                    $oAction->nome = $action->nome;
                                    $oAction->save();
                                }


                                // Verifica se já existe conexão entre permissão e action
                                $query = Doctrine_Query::create()
                                        ->from('Model_AclPermissaoAction')
                                        ->where('id_permissao = ?', $oPermissao->id)
                                        ->andWhere('id_action = ?', $oAction->id)
                                        ->count();
                                if (0 >= $query) {
                                    $oPA = new Model_AclPermissaoAction();
                                    $oPA->id_action = $oAction->id;
                                    $oPA->id_permissao = $oPermissao->id;
                                    $oPA->save();
                                }
                            }


                            // Percorre os menus
                            foreach ($permissao->AclPermissaoMenu as $permissao_menu) {
                                $menu = $permissao_menu->AclMenu;
                                // Verifica se o menu pai já existe
                                if (isset($menu->Pai)) {
                                    $pai = $menu->Pai;
                                    $oPai = Doctrine::getTable('Model_AclMenu')->findOneBy('texto', $pai->texto);
                                    if (!$oPai) {
                                        $oPai = new Model_AclMenu();
//                                        $oPai->janela = $pai->janela;
                                        $oPai->texto = $pai->texto;
                                        $oPai->icone = $pai->icone;
                                        $oPai->save();
                                    }
                                }
                                if (!isset($menu->janela)) {
                                    print_r($menu);
                                    throw new Exception("O menu {$menu->texto} não possui janela");
                                }
                                // verifica se o menu já existe
                                $oMenu = Doctrine::getTable('Model_AclMenu')->findOneBy('janela', $menu->janela);
                                if (!$oMenu) {
                                    $oMenu = new Model_AclMenu();
                                    $oMenu->janela = $menu->janela;
                                    $oMenu->texto = $menu->texto;
                                    $oMenu->icone = $menu->icone;
                                    $oMenu->pai = ($oPai) ? $oPai->id : NULL;
                                    $oMenu->save();
                                }


                                // Verifica se já existe conexão entre permissão e menu
                                $query = Doctrine_Query::create()
                                        ->from('Model_AclPermissaoMenu')
                                        ->where('id_permissao = ?', $oPermissao->id)
                                        ->andWhere('id_menu = ?', $oMenu->id)
                                        ->count();
                                if (0 >= $query) {
                                    $oPM = new Model_AclPermissaoMenu();
                                    $oPM->id_menu = $oMenu->id;
                                    $oPM->id_permissao = $oPermissao->id;
                                    $oPM->save();
                                }
                            }
                        }
                    }
                }
            }
            $response->sucesso = 1;
        } catch (Exception $e) {
            $response->sucesso = 0;
            $response->erro = $e->getMessage();
            $response->trace = $e->getTraceAsString();
        }
        echo json_encode($response);
    }

    /**
     * Lista as permissões cadastradas. 
     */
    public function permissoesAction() {
        $post = json_decode(json_encode($_POST));

        $query = Doctrine_Query::create()
                ->from('Model_AclPermissao a')
        ;

        if (isset($post->id) && "" != $post->id) {
            $query->addWhere("a.id  = :id", Array(
                ':id' => $post->id
            ));
        }

        // Descrição
        if (isset($post->descricao) && !in_array($post->descricao, Array("", null, "null"))) {
            $query->addWhere("a.descricao LIKE :descricao", Array(
                ':descricao' => "%{$post->descricao}%"
            ));
        }

        // Comentário
        if (isset($post->comentario) && "" != $post->comentario) {
            $query->addWhere("a.comentario LIKE :comentario", Array(
                ':comentario' => "%{$post->comentario}%"
            ));
        }

        // Ordenação
        $query->orderBy("a.descricao ASC");
        // Cria o paginador
        //Executa com paginação
        $pager = new Doctrine_Pager(
                $query, 1, 1000
//                        $_POST['page_current'],
//                        $_POST['page_maxsize']
        );

        $retorno = new Response($pager);
        echo json_encode($retorno);
    }

    /**
     * Lista os menus cadastrados. 
     */
    public function menusAction() {
        $post = json_decode(json_encode($_POST));

        $query = Doctrine_Query::create()
                ->from('Model_AclMenu m')
                ->leftjoin('m.AclMenu p')
                ->leftjoin('p.AclMenu a')
                ->where('m.pai IS NULL')
        ;

        if (isset($post->id) && "" != $post->id) {
            $query->addWhere("m.id  = :id", Array(
                ':id' => $post->id
            ));
        }

        // Janela
        if (isset($post->janela) && "" != $post->janela) {
            $query->addWhere("m.janela LIKE :janela", Array(
                ':janela' => "%{$post->janela}%"
            ));
        }

        // Texto
        if (isset($post->texto) && "" != $post->texto) {
            $query->addWhere("m.texto LIKE :texto", Array(
                ':texto' => "%{$post->texto}%"
            ));
        }

        // Ordena
        $query->orderBy('pai')
                ->addOrderBy("m.ordem")
                ->addOrderBy("p.ordem")
                ->addOrderBy("a.pai");

        //Executa com paginação
        $pager = new Doctrine_Pager(
                $query, $_POST['page_current'], $_POST['page_maxsize']
        );

        header('Content-Type: application/json');
        $retorno = new Response($pager);
        echo json_encode($retorno);
    }

    /**
     * Lista os papéis cadastradas. 
     */
    public function papeisAction() {
        $post = json_decode(json_encode($_POST));

        $query = Doctrine_Query::create()
                ->from('Model_AclPapel p')
        ;

        if (isset($post->id) && (!in_array($post->id, Array(NULL, 'null', '')))) {
            $query->addWhere("p.id  = :id", Array(
                ':id' => $post->id
            ));
        }

        // Descrição
        if (isset($post->descricao) && (!in_array($post->descricao, Array(NULL, 'null', '')))) {
            $query->addWhere("p.descricao LIKE :descricao", Array(
                ':descricao' => "%{$post->descricao}%"
            ));
        }

        // Comentário
        if (isset($post->comentario) && (!in_array($post->comentario, Array(NULL, 'null', '')))) {
            $query->addWhere("p.comentario LIKE :comentario", Array(
                ':comentario' => "%{$post->comentario}%"
            ));
        }

        // Ordenação
        $query->orderBy("p.descricao ASC");
        // Cria o paginador
        $pager = new Doctrine_Pager(
                $query, $post->page_current, // Current page of request
                $post->page_maxsize// (Optional) Number of results per page. Default is 25
        );

        echo json_encode(new Response($pager));
    }

    /**
     * Retorna a permissão referente ao ID informado. 
     */
    public function lerpermissaoAction() {
        $post = $_POST;
        $dados = Array();
        $query = Doctrine_Query::create()
                        ->from('Model_AclPermissao p')
                        ->leftJoin("p.AclPermissaoAction pa")
                        ->leftJoin("pa.AclAction a")
                        ->leftJoin("p.AclPermissaoMenu pm")
                        ->leftJoin("pm.AclMenu m")
                        ->where("p.id  = :id", Array(
                            ':id' => $post['id']
                        ))
                        ->execute()->getFirst();
        if ($query) {
            $dados = $query->toArray();
        }
        echo json_encode($dados);
        die;
    }

    /**
     * Retorna o papel referente ao ID informado. 
     */
    public function lerpapelAction() {
        $post = json_decode(json_encode($_POST));

        $query = Doctrine_Query::create()
                ->from('Model_AclPapel p')
                ->leftJoin("p.AclPapelPermissao pp")
                ->leftJoin("pp.AclPermissao e")
                ->where("p.id  = :id", Array(
            ':id' => $post->id
        ));
        $dados = $query->execute()->getFirst()->toArray();
        echo json_encode($dados);
        die;
    }

    /**
     * Salva uma action. 
     */
    public function salvaractionAction() {
        $post = $_POST;
        $action = NULL;
        /* @var $action Model_AclAction */
        try {
            if ($post['id'] != 0) {
                $action = Doctrine_Core::getTable("Model_AclAction")->find($post["id"]);
            } else {
                $action = new Model_AclAction();
            }
            $action["nome"] = $post["url"];
            $action->save();
            $post["sucesso"] = 1;
        } catch (Exception $e) {
            $post["sucesso"] = 0;
            $post["desc"] = "Ocorreu um erro. Tente novamente!";
            $post["trace"] = $e->getTraceAsString();
            $post["erro"] = $e->getMessage();
        }
        $post["sucesso"] = (isset($post["sucesso"]) ? $post["sucesso"] : 1);
        echo json_encode($post);
        die;
    }

    /**
     * Salva uma permissao. 
     */
    public function salvarpermissaoAction() {
        $dados = W3brUtils::getJson($_POST, 'dados');
        $obj = NULL;
        /* @var $action Model_AclAction */
        if (empty($dados->description)) {
            throw new Exception('O campo \'Descrição\' é obrigatório!');
        }

        if (isset($dados->id) && $dados->id != 0) {
            $obj = Doctrine_Core::getTable("Model_AclPermissao")->find($dados->id);
        } else {
            $obj = new Model_AclPermissao();
        }
        $obj["descricao"] = $dados->description;
        $obj->save();

        //Limpa as actions vinculadas
        Doctrine_Query::create()
                ->delete("Model_AclPermissaoAction a")
                ->where("a.id_permissao = ?", $obj->id)
                ->execute();

        // Vincula as novas actions
        foreach ($dados->actions as $action) {
            $oAction = new Model_AclPermissaoAction;
            $oAction->id_permissao = $obj->id;
            $oAction->id_action = $this->checkAction($action);
            $oAction->save();
        }

        //Limpa os menus vinculados
        Doctrine_Query::create()
                ->delete("Model_AclPermissaoMenu a")
                ->where("a.id_permissao = ?", $obj->id)
                ->execute();

        // Vincula os novos menus
        foreach ($dados->menus as $menu) {
            $oMenu = new Model_AclPermissaoMenu;
            $oMenu->id_permissao = $obj->id;
            $oMenu->id_menu = $menu;
            $oMenu->save();
        }

        $response = Response::getInstance();
        $response->sucesso = 1;
    }

    /**
     * Salva um item do menu. 
     */
    public function salvarmenuAction() {
        $dados = $_POST;
        $obj = NULL;
        $response = new Response();
        /* @var $action Model_AclMenu */
        try {
            if (isset($dados['id']) && $dados['id'] != 0) {
                $obj = Doctrine_Core::getTable("Model_AclMenu")->find($dados['id']);
            } else {
                $obj = new Model_AclMenu();
            }
            $obj->icone = $dados['icone'];
            $obj->janela = $dados['janela'];
            $obj->texto = $dados['texto'];
            $obj->pai = $dados['pai'] != 0 ? $dados['pai'] : NULL;
            $obj->ordem = 9999;
            $obj->save();

            $response->sucesso = 1;
        } catch (Exception $e) {
            $response->sucesso = 0;
            $response->trace = $e->getTraceAsString();
            $response->erro = $e->getMessage();
        }
        echo json_encode($response);
    }

    /**
     * Muda a posição de um ou mais menu(s).
     */
    public function posicionarAction() {
        $dados = W3brUtils::getJson($_POST, 'dados');

        // Busca os menus a serem movidos
        $alvos = Doctrine_Query::create()
                ->from("Model_AclMenu")
                ->whereIn('id', $dados->ids)
                ->orderBy("ordem")
                ->execute();

        // Busca todos os menus
        $menus = Doctrine_Query::create()
                ->from("Model_AclMenu")
                ->orderBy('pai')
                ->addOrderBy('ordem')
                ->execute();

        // Encontra a posicao a ser inserida
        $novos = Array();
        foreach ($menus as $key => $menu) {
            if (!in_array($menu->id, $dados->ids)) {
                $novos[] = $menu;
            }
            if ($dados->row == $menu->id) {
                foreach ($alvos as $alvo) {
                    $novos[] = $alvo;
                }
            }
        }

        // Salva a nova ordem
        foreach ($novos as $key => $novo) {
            $novo->ordem = $key;
            $novo->save();
        }

        // Envia a resposta ao navegador
        $response = new Response();
        $response->sucesso = 1;
        echo json_encode($response);
    }

    /**
     * Verifica se a action já existe, se não existir cria e retorna o ID.
     * @todo Excluir as actions que não possuírem vinculo com nenhuma permissão.
     * @param Object $action
     * @return Intenger
     */
    private function checkAction($action) {
        $obj = new Model_AclAction();
        if (0 == $action->id) {
            // Se for uma nova action insere
            $obj = new Model_AclAction();
            $obj->nome = $action->nome;
            $obj->save();
        } else {
            $obj = Doctrine::getTable('Model_AclAction')->find($action->id);
            if (!($obj && $action->nome == $obj->nome)) {
                // Se for uma nova action insere
                $obj = new Model_AclAction();
                $obj->nome = $action->nome;
                $obj->save();
            }
        }
        return $obj->id;
    }

    /**
     * Salva uma permissao. 
     */
    public function salvarpapelAction() {
        $post = $_POST;
        $permissoes = array_unique(json_decode(urldecode($post['permissoes'])));

        // Valida a quantidade de permissões
        if (0 >= count($permissoes)) {
            throw new Exception('Selecione ao menos uma permissão!');
        }
        $obj = NULL;
        /* @var $obj Model_AclPapel */
        if (isset($post['id']) && $post['id'] != 0) {
            $obj = Doctrine_Core::getTable("Model_AclPapel")->find($post['id']);
        } else {
            $obj = new Model_AclPapel();
        }
        $obj["descricao"] = $post['descricao'];
        $obj->save();

        //Limpa as actions vinculadas
        Doctrine_Query::create()
                ->delete("Model_AclPapelPermissao a")
                ->where("a.id_papel = ?", $obj->id)
                ->execute();
        foreach ($permissoes as $permissao) {
            $oPermissao = new Model_AclPapelPermissao;
            $oPermissao->id_papel = $obj->id;
            $oPermissao->id_permissao = $permissao;
            $oPermissao->save();
        }
        $response = Response::getInstance();
        $response->sucesso = 1;
    }

}
