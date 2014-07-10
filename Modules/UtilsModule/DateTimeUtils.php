<?php

/**
 * Funções personalizadas para conversão de data e hora
 *
 * @author Leandro Chaves<leandro.chaves@h2asol.com>
 */
class DateTimeUtils {

    private function eFeriado(DateTime $data, $feriados) {
        foreach ($feriados as $dia) {
            if ($data->format('Ymd') == $dia['data']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Cria um objeto DateTime com a data informada no formato d/m/Y.
     * @param String $data Data no formato d/m/Y
     * @return DateTime Objeto com a data informada.
     */
    public static function converterData($data) {
        $obj = new DateTime();
        try {
            $values = Array();
            if (preg_match('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{1,4})/', $data, $values)) {
                array_shift($values);
                $t = $values;
                if (checkdate($t[1], $t[0], $t[2])) {
                    $obj = new DateTime($t[2] . '-' . $t[1] . '-' . $t[0]);
                }
            }
        } catch (Exception $e) {
            $obj = new DateTime();
        }
        return $obj;
    }

    /**
     * Calcula a diferença em horas comerciais entre a primeira e a segunda data passadas como parâmetro
     * @param DateTime $start Data inicial
     * @param DateTime $end Data final
     * @param Array $feriados Formato: Array(Array('data'=>'20121225','desc'=>'Natal'))
     * @param DateTime $inicio Hora de inicio do horário comercial, padrão = 8
     * @param DateTime $fim Hora de término do horário comercial, padrão = 18
     * @return Array Array com horas e minutos
     */
    public static function horasUteis(DateTime $start, DateTime $end, $feriados = Array(), $inicio = '8:00', $fim = '18:00') {
        $step = $start;
        $seguinte = $start;
        $horas = 0;

        $hora_inicio = strtotime($step->format('Y-m-d') . ' ' . $inicio);
        while ($step <= $end) {
            // Hora inicial e final no dia atual
            $hora_inicio = strtotime($step->format('Y-m-d') . ' ' . $inicio);
            $hora_fim = strtotime($step->format('Y-m-d') . ' ' . $fim);

            // Se a hora atual estiver dentro do horario comercial
            // E o dia não for domingo
            if (($step->format('U') < $hora_fim) && ($step->format("w") != 0) && (!DateTimeUtils::eFeriado($step, $feriados))) {
                if ($step->format('U') >= $hora_inicio) {
                    $inicial = $step->format('U');
                    $step = new DateTime($step->format('Y-m-d'));
                } else {
                    $inicial = $hora_inicio;
                }
                // Se a hora estiver abaixo do horário comercial
                if ($step->format('U') < $hora_fim) {
                    if (strtotime($end->format('y-m-d')) == strtotime($step->format('Y-m-d'))) {
                        if ($end->format('U') > $hora_inicio) {
                            $final = $end->format('U');
                        } else {
                            $final = $hora_inicio;
                        }
                    } else {
                        $final = $hora_fim;
                    }
                }
                if ($final > $hora_fim) {
                    $horas += ( $hora_fim - $inicial);
                } else {
                    $horas += ( $final - $inicial);
                }
            } else {
                $step = new DateTime($step->format('Y-m-d'));
            }
            $step->modify('+1 day');
        }
        $horas = $horas / 3600;
        $min = ($horas - (int) $horas) * 60;
        $horas = (int) $horas;
        $retorno = array('h' => $horas, 'm' => $min);
        return $retorno;
    }

    /**
     * Calcula a diferença em horas comerciais entre a primeira e a segunda data passadas como parâmetro
     * @param DateTime $data1 Data inicial
     * @param DateTime $data2 Data final
     * @param DateTime $inicio Hora de inicio do horário comercial, padrão = 8
     * @param DateTime $fim Hora de término do horário comercial, padrão = 18
     * @return Array Array com horas e minutos
     */
    public static function horario_comercial($data1, $data2, $inicio = 8, $fim = 18) {
        $tempo[1] = $data1->format('U');
        $tempo[2] = $data2->format('U');

        $minutos = 0;
        $segs = date('s', $tempo[1]);

        if ($segs != '00' || $segs != '0' || $segs != 0 || $segs != 00) {
            $falta = 60 - $segs;
            $tempo[1] += $falta;
        }

        while ($tempo[1] < $tempo[2]) {
            $tempo[1] += 60;
            if (date('H', $tempo[1]) >= $fim) {
                $tempo[1] = mktime($inicio, 0, 0, date('m', $tempo[1]), date('d', $tempo[1]) + 1, date('Y', $tempo[1]));
                if (date('w', $tempo[1]) == 0) {
                    $tempo[1] = mktime($inicio, 0, 0, date('m', $tempo[1]), date('d', $tempo[1]) + 1, date('Y', $tempo[1]));
                }
            }
            $minutos++;
        }

        $min_temp = $minutos;
        $horas = 0;
        while ($min_temp >= 60) {
            $horas++;
            $min_temp-=60;
        }
        $retorno = array('h' => $horas, 'm' => $min_temp);
        return $retorno;
    }

}

?>