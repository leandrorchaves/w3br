<?php

/**
 * Controle de exibição de log
 * @author Leandro Chaves<leandro.chaves@h2asol.com>
 */
class LogController {

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

        $log = fopen(DIR_RAIZ . 'logs/access.' . date('Ymd') . '.txt', 'r');
        $ip = '';
        $hora = '';
        $user = '';
        $tempo = '';
        $rows = Array();
        while ($linha = fgets($log)) {
            $matches = Array();
            if (preg_match("/\[([0-9.]+)\]\[([0-9 :-]+)\]\[(.*)\]\[END\]\[.*\] ([0-9.]+)/", $linha, $matches)) {
                $ip = $matches[1];
                $hora = $matches[2];
                $user = $matches[3];
                $tempo = $matches[4];
                $rows[] = '[new Date(\'' . $hora . '\'), ' . $tempo . ', \'URL\', \'\']';
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

}

?>
