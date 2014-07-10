<?php

/**
 * Modelo de Gerenciador de Páginas
 *
 * @author Leandro Chaves <leandrorchaves@gmail.com>
 * @link http://leandrorchaves.com
 */
class _DefaultController {

    protected $titulo;
    protected $tabela;
    protected $busca;
    protected $campos;
    protected $dependencias;

    function __construct() {
        W3br::loadClass('LoginController');
        W3br::loadClass('ContainerController');
    }

    /**
     * Apresenta a tela de Inserção
     * @param Array $url
     */
    function inserir($url) {
        LoginController::validar();
        //View
        $container = new ContainerController;
        foreach ($this->dependencias as $dep) {
            $itens = Doctrine_Query::create()
                            ->from('Model_' . $dep['tabela'])
                            ->execute();
            if ($itens) {
                $tbls['Model_' . $dep['tabela']] = $itens->toArray();
            }
        }
        $container->smarty->assign('acao', 'inserir');
        $container->smarty->assign($tbls);
        $container->smarty->display($this->titulo . '/incluir.html');
    }

    /**
     * Efetiva a inserção de Usuário
     * @param Array $url
     */
    function inserir_salvar($url) {
        LoginController::validar();
        $classe = ucfirst($this->tabela);
        $novo = new $classe();
        $campos = $novo->toArray();
        while (list($key, $val) = each($campos)) {
            if (isset($_POST[$key])) {
                $novo->$key = ($_POST[$key] != '' ? $_POST[$key] : NULL);
            }
        }
        try {
            $novo->save();
            echo '0|Inserção concluída com sucesso.';
        } catch (Exception $e) {
            if ($e->getCode() == '23000') {
                echo '1|Erro na inserção.';
            }
        }
    }

    /**
     *  Lista os Usuários do sistema
     * @param String[] $url Dados para paginação
     */
    function listar($url) {
        LoginController::validar();
        $regs = Doctrine_Query::create()
                        ->from($this->tabela . ' r');
        foreach ($this->dependencias as $dep) {
            $regs->leftJoin('r.' . $dep['tabela']); //.' '.$dep['alias']
        }
        $total = $regs->count();

        //Monta a Busca
        $texto = '%' . str_replace(' ', '%', $_GET['sSearch']) . '%';

        //Ordena o resultado
        $order = NULL;
        //if($_GET['iSortCol_0']>='0')$order=$_GET['iSortCol_0'].' '.$_GET['sSortDir_0'];
        $where = array();
        while (list($chave, $valor) = each($this->busca)) {
            $where[] = $valor . " LIKE '$texto' ";
        }
        $regs->where(implode('OR ', $where));
        if ($order != null) {
            $regs->orderBy($order);
        }
        $filtro = $regs->count();

        // Cria o paginador
        $pagina = ($_GET['iDisplayStart'] / $_GET['iDisplayLength']) + 1;
        $pager = new Doctrine_Pager(
                        $regs,
                        $pagina, // Current page of request
                        $_GET['iDisplayLength'] // (Optional) Number of results per page. Default is 25
        );
        $registros = $pager->execute();
        //Gerando um JSON
        $sOutput = '{';
        $sOutput .= '"sEcho": ' . intval($_GET['sEcho']) . ', ';
        $sOutput .= '"iTotalRecords": ' . $total . ', ';
        $sOutput .= '"iTotalDisplayRecords": ' . $filtro . ', ';
        $sOutput .= '"aaData": [ ';
        $virgula = false;
        $saida = array();
        foreach ($registros as $linha) {
            $tabela = array();
            $linkEditar = "<img style=\\\"cursor:pointer;\\\" src=www/imagens/editar.png onclick=\\\"return dialog('?q=" . $this->titulo . "/editar/" . $linha['id'] . "', 'inserir_" . $this->titulo . "', 500, 160, 'Editar " . $this->titulo . "');\\\">";
            $linkExcluir = "<img style=\\\"cursor:pointer;\\\" src=www/imagens/excluir.png onclick=\\\"return " . $this->titulo . ".confirmarExcluir(" . $linha['id'] . ");\\\">";
            $campos = $this->campos;
            while (list($chave, $valor) = each($campos)) {
                if (is_array($valor)) {
                    $tabela[] = '"' . $linha[$valor[0]][$valor[1]] . '"';
                } else {
                    $tabela[] = '"' . $linha[$valor] . '"';
                }
            }
            $tabela[] = '"' . $linkEditar . '"';
            $tabela[] = '"' . $linkExcluir . '"';
            $saida[] = '[' . implode(',', $tabela) . ']';
        }
        $sOutput .= implode(',', $saida);
        $sOutput .= ' ]}';
        echo $sOutput;
    }

    /**
     *  Retorna um Objeto JSON com os Clientes do sistema
     * @param String[] $url Dados para paginação
     */
    function json($url) {
        LoginController::validar();
        // Cria o paginador
        $pager = new Doctrine_Pager(
                                Doctrine_Query::create()
                                ->from($this->tabela . ' r'),
                        1, // Current page of request
                        50 // (Optional) Number of results per page. Default is 25
        );
        $registros = $pager->execute()->toArray();
        echo json_encode($registros);
    }

    /**
     * Apresenta a listagem de Itens
     * @param Array $url 
     */
    function buscar($url) {
        LoginController::validar();
        //View
        $container = new ContainerController;
        $container->header();
        $container->smarty->display($this->titulo . '/listar.html');
        $container->footer();
    }

    /**
     * Apresenta a lista para seleção
     * @param Array $url
     */
    function selecionar($url) {
        LoginController::validar();
        //View
        $container = new ContainerController;
        $container->smarty->display($this->titulo . '/selecionar.html');
    }

    /**
     *  Retorna um Objeto JSON com o nome dos objetos para seleção
     * @param String[] $url Dados para paginação
     */
    function json_selecionar($url) {
        LoginController::validar();
        $regs = Doctrine_Query::create()
                        ->from($this->tabela . ' r');
        $total = $regs->count();

        //Monta a Busca
        $texto = '%' . str_replace(' ', '%', $_GET['sSearch']) . '%';

        //Ordena o resultado
        $order = NULL;
        //if($_GET['iSortCol_0']>='0')$order=$_GET['iSortCol_0'].' '.$_GET['sSortDir_0'];
        if ($order != null) {
            $regs->orderBy($order);
        }
        $where = array();
        while (list($chave, $valor) = each($this->busca)) {
            $where[] = $valor . " LIKE '$texto' ";
        }
        $regs->where(implode('AND ', $where));
        $filtro = $regs->count();

        // Cria o paginador
        $pager = new Doctrine_Pager(
                        $regs,
                        ($_GET['iDisplayStart'] / $_GET['iDisplayLength']), // Current page of request
                        100 // (Optional) Number of results per page. Default is 25
        );
        $registros = $pager->execute();

        //Gerando um JSON
        $sOutput = '{';
        $sOutput .= '"sEcho": ' . intval($_GET['sEcho']) . ', ';
        $sOutput .= '"iTotalRecords": ' . $total . ', ';
        $sOutput .= '"iTotalDisplayRecords": ' . $filtro . ', ';
        $sOutput .= '"aaData": [ ';
        $virgula = false;
        $saida = '';
        foreach ($registros as $linha) {
            $link = "<a style=\\\"cursor:pointer;\\\"  href onclick=\\\"return $this->titulo.selecionarConfirmar('" . $linha['id'] . "', '" . $linha['nome'] . "');\\\">" . $linha['nome'] . "</a>";
            $saida[] = '["' . $link . '"]';
        }
        $sOutput .= implode(',', $saida);
        $sOutput .= ' ]}';
        echo $sOutput;
    }

    /**
     * Apresenta a tela de Edição
     * @param Array $url
     */
    function editar($url) {
        LoginController::validar();
        $tbls = array();
        $reg = Doctrine_Core::getTable($this->tabela)->find($url[2]);
        $tbls['reg'] = $reg->toArray();
        $deps = $this->dependencias;
        foreach ($deps as $dep) {
            $dependencias = Doctrine_Core::getTable('Model_' . $dep['tabela']);
            if (isset($dep['campo'])) {
                $itens = Doctrine_Query::create()
                                ->from('Model_' . $dep['tabela'])
                                ->execute();
                if ($itens) {
                    $tbls['Model_' . $dep['tabela']] = $itens->toArray();
                }
            } else {
                $tbls['Model_' . $dep['tabela']] = $dependencias->findAll()->toArray(false);
            }
        }
        $tbls['acao'] = 'editar';
        //View
        $container = new ContainerController;
        $container->smarty->assign('acao', 'editar');
        $container->smarty->assign('reg', $reg);
        $container->smarty->assign($tbls);
        $container->smarty->display($this->titulo . '/incluir.html');
    }

    /**
     * Efetiva a Alteraçao de um registro
     * @param Array $url
     */
    function editar_salvar($url) {
        LoginController::validar();
        if (isset($_POST['id'])) {
            $reg = new Usuarios();
            $reg = Doctrine_Core::getTable($this->tabela)->find($_POST['id']);
            $campos = $reg->toArray();
            while (list($key, $val) = each($campos)) {
                if (isset($_POST[$key])) {
                    $reg->$key = ($_POST[$key] != '' ? $_POST[$key] : NULL);
                }
            }
            try {
                $reg->save();
                echo '0|Item alterado com sucesso.';
            } catch (Exception $e) {
                if ($e->getCode() == '23000') {
                    echo '1|Campo inválida.';
                }
            }
        }
    }

    /**
     * Efetiva a exclusao de um registro
     * @param Array $url 
     */
    function excluir($url) {
        LoginController::validar();
        $reg = Doctrine_Core::getTable($this->tabela)->find($url[2]);
        if ($reg) {
            $reg->delete();
        }
        echo 'ok';
    }

    function exportar($url) {
        $regs = Doctrine_Query::create()
                        ->from($this->tabela . ' r');
        $total = $regs->count();

        //Monta a Busca
        $texto = '%' . str_replace(' ', '%', isset($_GET['sSearch']) ? $_GET['sSearch'] : '') . '%';

        //Ordena o resultado
        $order = NULL;
        //if($_GET['iSortCol_0']>='0')$order=$_GET['iSortCol_0'].' '.$_GET['sSortDir_0'];
        $where = array();
        $and = array();
        foreach ($this->busca as $chave) {
            $where[] = $chave . " LIKE '$texto' ";
            if (isset($_GET[$chave])) {
                $where[] = $chave . " = '$_GET[$chave]' ";
            }
        }
        $regs->where(implode('AND ', $where));
        if ($order != null) {
            $regs->orderBy($order);
        }
        $filtro = $regs->count();

        // Cria o paginador
        $pager = new Doctrine_Pager(
                        $regs,
                        (isset($_GET['Page']) ? $_GET['Page'] : 1), // Actual Page
                        (isset($_GET['Length']) ? $_GET['Length'] : 10) // (Optional) Number of results per page. Default is 10
        );
        $registros = $pager->execute();
        $saida->TotalRecords = $total;
        $saida->DisplayRecords = $pager->getResultsInPage();
        $saida->itens = $registros->toArray();
        echo json_encode($saida);
    }

}

?>
