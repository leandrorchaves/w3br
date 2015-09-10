<?php

/**
 * Doctrine Controller
 *
 * @author Leandro Chaves
 */
class DoctrineController {

    /**
     * Generate Doctrine models
     * @param String[] $url
     */
    function reverseAction() {
        $title = 'Models generated successfully!';
        $msg = '';
        try {

            Doctrine::generateModelsFromDb(DOCTRINE_MODELSPATH, array(), array('baseClassPrefix' => "Base", 'classPrefix' => 'Model_'));
        } catch (Doctrine_Import_Builder_Exception $e) {
            $title = 'Failure to generate models!';
            $msg = $e->getMessage();
        }
        print $title . "\n" . $msg;
        //die;
    }

    /**
     * Executa a função phpinfo().
     */
    function phpinfoAction() {
        phpinfo();
    }

    /**
     * Analisa o log de querys e apresenta o resultado.
     */
    function logAction() {
        $data = isset($_GET['data']) ? $_GET['data'] : date('Ymd');
        $file = fopen('./logs/querys/' . $data . '.txt', 'r');
        $url = '';
        while ($linha = fgets($file)) {
            $matches = Array();
            if (preg_match("/\[([0-9.]*)\]\[([0-9 :-]*)\] \[Execution\] ([0-9.]*) \[Query\] (.*)/", $linha, $matches)) {
                if (0.5 < $matches[3]) {
                    echo "[{$matches[1]}][{$matches[2]}][{$matches[3]}]<br/>\n";
                }
            }
        }
    }

    function graficoAction() {
        $log = $this->parseLog();
        $grafico = $this->montaGrafico($log);
        echo "<html id='html'>
                <head>{$grafico}
                </head>
                <body id='body'>
                    <center>
                    <!--Div that will hold the pie chart-->
                    <div id='chart_div' style='width: 1200px; height: 600px;'></div>
                    </center>
                    <script type='text/javascript'>
                        document.getElementById('chart_div').style.width = window.innerWidth -50;
                        document.getElementById('chart_div').style.height = window.innerHeight - 50;
                    </script>
                </body>
            </html>";
    }

    private function parseLog() {

        $log = fopen(ROOT_DIR . 'logs/querys/' . date('Ymd') . '.txt', 'r');
        $hora = '';
        $url = '';
        $rows = Array();
        while ($linha = fgets($log)) {
            $matches = Array();
            if (preg_match("/\[([0-9.]+)\]\[([0-9 :-]+)\] \[START CONNECTION '(.*)'\]/", $linha, $matches)) {
                $hora = $matches[2];
                $url = $matches[3];
            }
            if (preg_match("/\[([0-9.]+)\]\[([0-9 :-]+)\].*\[Tempo Total\] ([0-9.]+)/", $linha, $matches)) {
                $tempo = $matches[3];
                $rows[] = '[new Date(\'' . $hora . '\'), ' . $tempo . ', \'URL\', \''.$url.'\']';
            }
        }
        return $rows;
    }

    private function montaGrafico($logs) {
        $values = implode(',', $logs);
        return "
            <!--Load the AJAX API-->
            <script type=\"text/javascript\" src=\"https://www.google.com/jsapi\"></script>
               <script type=\"text/javascript\">
                  // Load the Visualization API and the piechart package.
                  google.load('visualization', '1.0', {'packages':['annotatedtimeline']});

                  // Set a callback to run when the Google Visualization API is loaded.
                  google.setOnLoadCallback(drawChart);

                  // Callback that creates and populates a data table,
                  // instantiates the pie chart, passes in the data and
                  // draws it.
                  function drawChart() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('datetime', 'Date');
                    data.addColumn('number', 'Tempo de Execução');
                    data.addColumn('string', 'title1');
                    data.addColumn('string', 'text1');
                    data.addRows([{$values}]);

                    var chart = new google.visualization.AnnotatedTimeLine(
                        document.getElementById('chart_div'));
                    chart.draw(data, {'displayAnnotations': false});
                  }
                </script>
            ";
    }

    /**
     * 
     * @param Doctrine_Record $class Objeto para montar o select
     * @param String $value Campo que será utilizado como valor
     * @param String $label Campo que será utilizado como label
     * @param String $selected Valor do registro que será selecionado por padrão
     * @return string 
     */
    public static function buildOptions($class, $value, $label, $selected = '') {
        $str = '';
        foreach ($class as $o) {
            $str .= '<option value="' . $o->$value . '"';
            if ($o->$value == $selected) {
                $str .= ' selected="selected"';
            }
            $str .= '>' . $o->$label . '</option>' . PHP_EOL;
        }
        return $str;
    }

}

?>
