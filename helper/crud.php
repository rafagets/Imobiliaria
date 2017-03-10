<?php

require_once '../uteis/Util.php';
require_once '../helper/Regras.php';

class Crud {

    private $flag;
    private $util;

    public function __construct($email, $senha, $tabela) {
        // processo de autenticação no sistema;
        $func = new Metodos();
        $senha = $func->hashSenha($senha);

        if (!$func->autenticacao($email, $senha)) {
            $this->flag = FALSE;
        } else {
            $this->util = new Util($tabela);
            $this->flag = TRUE;
        }
    }

    public function getFlag() {
        return $this->flag;
    }

    /**
     * Prepara para ser salvo.
     * @param type $atributos   = "nome, idade, sexo"
     * @param type $valores     = "rafael, 21, M"
     * @param type $argumentos  = "sis" 
     * @return int = json formated ou com o valor 'LOGIN_ERROR'.
     */
    public function prepareCreate($atributos, $valores, $argumentos) {
        if ($this->flag) {
            return $this->util->create($atributos, $valores, $argumentos);
        } else {
            $this->flag = $this->flag['flag'] = 'LOGIN_ERROR';
            return $this->flag;
        }
    }

    /**
     * Prepara pra ler uma linha
     * @param type $atributos = "nome, sexo": É passado as linhas que se deseja obter com a consulta.
     * @param type $condicao = "codigo = 1".
     * @param type $ordenacao = "ORDER BY nome DESC" : Qual a ordenação que a consulta terá.
     * @param type $argumentos = "ssi" : argumentos dos atributos
     * @return type : retorna um jsonArray ou com o valor 'LOGIN_ERROR'.
     */
    public function prepareRead($atributos, $condicao, $ordenacao, $argumentos) {
        if ($this->flag) {
            return $this->util->read($atributos, $condicao, $ordenacao, $argumentos);
        } else {
            $this->flag = $this->flag['flag'] = 'LOGIN_ERROR';
            return $this->flag;
        }
    }

    /**
     * Prepara para atualizar uma linha.
     * @param type $atributos = "nome, sexo"    : colunas que se deseja alterar
     * @param type $valores = "rafael, 21"      : Valores das colunas. Esses valores devem estar na ordem com os atributos acima.
     * @param type $argumentos = "si"           : Os argumentos são os tipos dos dados que serão percistidos.
     * @param type $condicao = "codigo = 87"    : Qual a chve da linha que sera alterada.
     * @return int : Retorna um json com o numero de linhas afetadas ou com o valor 'LOGIN_ERROR'.
     */
    public function prepareUpdade($atributos, $valores, $argumentos, $condicao) {
        if ($this->flag) {
            return $this->util->update($atributos, $valores, $argumentos, $condicao);
        } else {
            $this->flag = $this->flag['flag'] = 'LOGIN_ERROR';
            return $this->flag;
        }
    }

    /**
     * Prapara deletar uma linha.
     * @param type $atributo    = "codigo" : A chave da linha que sera excluida
     * @param type $valor       = "21" : O valor da chave que sera excluida.
     * @param type $argumentos  = "1" : codigo da exclusao
     * @return int : Retorna um json com a quantidade de linhas afetadas ou com o valor 'LOGIN_ERROR'.
     */
    public function prepareDelete($atributo, $valor, $argumentos) {
        if ($this->flag) {
            return $this->util->delete($atributo, $valor, $argumentos);
        } else {
            $this->flag = $this->flag['flag'] = 'LOGIN_ERROR';
            return $this->flag;
        }
    }

}
