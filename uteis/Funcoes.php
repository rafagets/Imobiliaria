<?php

class Funcao {

    /**
     * Esse metodo tem a função de trazer as quantidades de variaveis que serão manipuladas
     * @param type $param = "nome, sexo"    : Recebe os parametros que serão manipilados
     * @return type = "?, ?"                : retorna uma String com as ? para mysqli->prepare.
     */
    function getInterrogacoes($param) {
        $qtdValores = explode(",", $param);
        foreach ($qtdValores as &$value) {
            $value = "? ";
        }
        $qtdValores = implode(",", $qtdValores);
        return $qtdValores;
    }

    /**
     * Esse metodo tem a função de formatar os campos que serão alterados no updade.
     * @param type $atributos = "nome, sexo" : colunas que serão manipuladas
     * @return type = "codigo = ?, sexo = ?" : Retorna texto formatado para prepare.
     */
    function setUpdate($atributos) {
        $val = explode(",", $atributos);

        $i = 0;
        foreach ($val as &$value) {
            $value .= " = ?";
        }
        $val = implode(", ", $val);

        return $val;
    }

    /**
     * Tem a funcao de trocas os valores por "?"
     * @param type $condicao : ex "codigo = 1, AND, nome = 2, OR, secao = 1"
     * @return type ex: "codigo = ? AND nome = ? OR secao = ?"
     */
    function prepareCondReadInterrogacao($condicao) {
        $val = explode(",", $condicao);
        $temp = "";
        foreach ($val as $value) {
            if (!in_array(trim($value), array('AND', 'OR'))) {
                $val2 = explode("=", $value);
                $val2[1] = " ?";
                $value = implode("=", $val2);
            }
            $temp .= $value . " ";
        }
        
        return $temp;
    }

    /**
     * Retorna uma array de valores
     * @param type $condicao ex "codigo = 1, AND, nome = 2, OR, secao = 1"
     * @return type array 
     */
    function prepareCondReadValues($condicao) {
        $val = explode(",", $condicao);
        $temp = "";
        foreach ($val as $value) {
            if (!in_array(trim($value), array('AND', 'OR'))) {
                $v = explode("=", $value);
                $temp .= $v[1] . ",";
            }
        }
        $temp = substr($temp, 0, strlen($temp) - 1);
        return explode(",", trim($temp));
    }

    /**
     * Pega a consulta IN() e retorna os valores passados;
     * @param type $string : contem dados para a consulta, ex: IN(1,3,5);
     * @return type
     */
    function getInValues($string) {
        preg_match('/(\([^\)]+\))/', $string, $match);
        $temp = $match[0];
        $temp = substr($temp, 1);
        $temp = substr($temp, 0, -1);
        $temp = explode(",", $temp);
        return $temp;
    }
    
    /**
     * Retorna a consulta in formatada para prepare staitmant;
     * @param type $string : contem dados para a consulta, ex: IN(1,3,5);
     * @return type : retorna a consulta modificada, ex: IN(?, ?, ?);
     */
    function getInSql($string) {
        preg_match('/(\([^\)]+\))/', $string, $match);
        $temp = $match[0];
        $temp = substr($temp, 1);
        $temp = substr($temp, 0, -1);

        $temp = explode(",", $temp);
        for ($i = 0; $i < count($temp); $i++) {
            $temp[$i] = "?";
        }

        $temp = "(" . implode(",", $temp) . ")";

        $temp = str_replace($match[0], $temp, $string);
        return $temp;
    }

    /**
     * Retorna os argumentos para consulta
     * @param type $string : contem dados para a consulta, ex: IN(1,3,5);
     * @return string : retorna os argumentos, ex: "iii";
     */
    function getInArgs($string) {
        $qtd = count($this->getInValues($string));
        $temp = "";
        for ($i = 0; $i < $qtd; $i++) {
            $temp .= "i";
        }        
        return $temp;            
    }

}
