<?php

/**
 * Objeto padrão de resposta ao navegador.
 *
 * @author Leandro Chaves
 */
class Response {

    const NOT_SUCCESS = 0;
    const SUCCESS = 1;

    /** Instência da classe singleton */
    private static $instance;
    public $sucesso;
    public $erro;
    public $trace;
    public $id;

    /** Array com os dados retornados na busca */
    public $dados;

    /** Última página da busca */
    public $page_last;

    /** Quantidade total de registros da busca */
    public $page_results;

    /** Tamanho da página atual */
    public $page_size;

    /** Primeiro item da página atual */
    public $page_start;

    /** Página Atual */
    public $page_current;

    /** Registros de log */
    public $log;

    /**
     * Retorna a instância da classe.
     * Só cria uma nova instância,quando não existir nenhuma.
     * @return Response
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function __construct(Doctrine_Pager $pager = null) {
//        parent::__construct();
        if (null == $this->sucesso) {
            $this->sucesso = Response::SUCCESS;
        }
        $this->pager($pager);
    }

    public function pager(Doctrine_Pager $pager = null) {
        if (null != $pager) {
            $this->dados = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
            $this->page_last = $pager->getLastPage();
            $this->page_results = $pager->getNumResults();
            $this->page_size = $pager->getResultsInPage();
            $this->page_start = $pager->getFirstIndice();
            $this->page_current = $pager->getPage();
        }
    }

    /**
     * Dispara o retorno para o navegador.
     */
    public static function send($format = 'json') {
        $self = self::getInstance();
        $self->log = Logger::getInstance()->get();
        switch ($format) {
            default: // O formato padrão de saída é json
                header('Content-type: aplication/json');
                echo json_encode(self::getInstance());
                break;
        }
    }

}