<?php

include_once 'Funcoes.php';
include_once 'Config.php';
include_once 'MinhasTabelas.php';

class Util
{

    private $con;
    private $tabela;
    private $funcoes;
    private $myfile;

    /**
     * Recebe a tabela que sera manipilada
     * O costrutor cria uma instancia da classe Funcao que contem funcoes uteis.
     * Depois elecria uma nova conexao mysqli e salva no atributo $con.
     * @param type $tabela : "usuario" : Nome da tabela que sera manipulada.
     */
    public function __construct($tabela)
    {
        // Verifica se a tabela existe no banco de dados. se sim, segue o codigo ou para o programa.
        if ((new Tabelas ())->isExist($tabela)) {
            $this->tabela = $tabela;
            $this->funcoes = new Funcao();

            /**
             * Tenta criar uma conexão com o Banco de Dados.
             * Para mudar o tipo de banco para firebird, basta trocar DB_DSN_MYSQL por DB_DSN_FIREBIRD
             * caso de um erro, retorna um json com o erro e para a execução.
             */
            try {
                $this->con = new PDO(DB_DSN_MYSQL, DB_USER, DB_PASSWORD);
            } catch (PDOException $exception) {
                $flag['flag'] = 'CONN_FAILED';
                die(json_encode($flag));
            }
        } else {
            $flag['flag'] = 'INVALID_TABLE';
            die(json_encode($flag));
        }

        // abri o log para registro dos updates
        $this->myfile = fopen("log.txt", "a") or die("Falha no log!");
    }

    /**
     * Insere um registro no banco de dados.
     * @param type $atributos = "nome, idade, sexo"
     * @param type $valores = "rafael, 21, M"
     * @param type $argumentos = "sis"
     * @return int retorna o id do objeto inserido
     */
    public function create($atributos, $valores, $argumentos)
    {
        $json['flag'] = 0;

        $qtdValores = $this->funcoes->getInterrogacoes($atributos);
        $valores = explode(",", $valores);
        $typeValues = $this->funcoes->getTypesArray($argumentos);

        $sql = "INSERT INTO $this->tabela ($atributos) VALUES ($qtdValores)";
        if ($stmt = $this->con->prepare($sql)) {
            $i = 0;
            foreach ($valores as &$valor) {
                $stmt->bindParam($i + 1, $valor, $typeValues[$i]);
                $i++;
            }
            $stmt->execute();
            $json['flag'] = $this->con->lastInsertId();
        }

        $stmt = null;
        $this->con = null;

        fwrite($this->myfile, "   SQL: " . $sql . "\n");
        return $json;
    }

    /**
     * Le um registro do banco de dados e retorna um json;
     * @param string $atributos = "nome, sexo": É passado as linhas que se deseja obter com a consulta.
     * @param type $condicao = ex: "cod = 1, nome = rafael" ou "" (vazio) para listar tudo;
     * @param type $ordenação = "ORDER BY nome DESC" : Qual a ordenação que a consulta terá.
     * @return array
     */
    public function read($atributos, $condicao, $ordenacao, $argumentos)
    {
        $sql = null;
        $stmt = null;
        $result = null;

        if (empty($atributos)) {
            $atributos = "*";
        }

        $execute = FALSE;
        if (empty($argumentos)) {
            $pos = strpos($condicao, "->");
            if ($pos === 0) {
                $sql = "SELECT $atributos FROM $this->tabela $ordenacao WHERE " . str_replace("->", "", $condicao);
            } else {
                $sql = "SELECT $atributos FROM $this->tabela $ordenacao";
            }
            $stmt = $this->con->prepare($sql);
            $execute = TRUE;
        } else if (!empty($condicao)) {
            $func = new Funcao();
            $values = $func->prepareCondReadValues($condicao);
            $condicao = $func->prepareCondReadInterrogacao($condicao);
            $typeValues = $this->funcoes->getTypesArray($argumentos);

            $condicao = " WHERE " . $condicao;
            $sql = "SELECT $atributos FROM $this->tabela $condicao $ordenacao";
            $stmt = $this->con->prepare($sql);

            $i = 0;
            foreach ($values as &$valor) {
                $stmt->bindParam($i + 1, $valor, $typeValues[$i]);
                $i++;
            }
            $execute = TRUE;
        }

        if ($execute === TRUE) {
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($execute === FALSE) {
            $result['flag'] = "ERROR_PREPARE";
        }

        if (empty($result)) {
            $result['flag'] = "SEM_RESULT";
        }

        $stmt = null;
        $this->con = null;

        fwrite($this->myfile, "   SQL: " . $sql . "\n");
        return $result;
    }

    /**
     * Atualiza os dados de uma linha do banco de dados.
     * @param type $atributos = "nome, sexo"    : colunas que se deseja alterar
     * @param type $valores = "rafael, 21"      : Valores das colunas. Esses valores devem estar na ordem com os atributos acima.
     * @param type $argumentos = "si"           : Os argumentos são os tipos dos dados que serão percistidos.
     * @param type $condicao = "codigo = 87"    : Qual a chve da linha que sera alterada.
     * @return int : Retorna um json com o numero de linhas afetadas.
     */
    public function update($atributos, $valores, $argumentos, $condicao)
    {
        $json['flag'] = 0;
        $atributos = $this->funcoes->setUpdate($atributos);
        $condicao = explode("=", $condicao);

        $valores .= ", $condicao[1]";
        $valores = explode(",", $valores);
        $typeValues = $this->funcoes->getTypesArray($argumentos);

        $sql = "UPDATE $this->tabela SET $atributos WHERE $condicao[0] = ?";
        if ($stmt = $this->con->prepare($sql)) {
            $i = 0;
            foreach ($valores as &$valor) {
                $stmt->bindParam($i + 1, $valor, $typeValues[$i]);
                $i++;
            }
            $stmt->execute();
            $json['flag'] = $stmt->rowCount();
        }

        $stmt = null;
        $this->con = null;

        fwrite($this->myfile, "   SQL: " . $sql . "\n");
        return $json;
    }

    /**
     * Exclui uma linha do banco de dados.
     * @param type $atributo = "codigo" : A chave da linha que sera excluida
     * @param type $valor = "21"        : O valor da chave que sera excluida.
     * @param type $argumentos = "i"    : argumen da chave que sera excluida.
     * @return int : Retorna um json com a quantidade de linhas afetadas.
     */
    public function delete($atributo, $valor, $argumentos)
    {
        $json['flag'] = 0;
        $typeValues = $this->funcoes->getTypesArray($argumentos);

        $sql = "DELETE FROM $this->tabela WHERE $atributo = ?";
        if ($stmt = $this->con->prepare($sql)) {
            $stmt->bindParam(1, $valor, $typeValues[0]);
            $stmt->execute();
            $json['flag'] = $stmt->rowCount();
        }

        $stmt = null;
        $this->con = null;

        fwrite($this->myfile, "   SQL: " . $sql . "\n");
        return $json;
    }

}
