<?php

include_once 'Funcoes.php';
include_once 'Config.php';
include_once 'MinhasTabelas.php';

class Util {

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
    public function __construct($tabela) {
        // Verifica se a tabela existe no banco de dados. se sim, segue o codigo ou para o programa.
        if ((new Tabelas ())->isExist($tabela)) {
            $this->tabela = $tabela;
            $this->funcoes = new Funcao();

            // Cria uma conexão;
            $this->con = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            // Verifica conexão
            if ($this->con->connect_error) {
                $flag['flag'] = 'CONN_FAILED';
                //die("Connection failed: " . $conn->connect_error);
                die(json_encode($flag));
            }
        } else {
            $flag['flag'] = 'INVALID_TABLE';
            die(json_encode($flag));
        }
        
        $this->myfile = fopen("log.txt", "a") or die("Falha no log!");
    }

    /**
     * Insere um registro no banco de dados.
     * @param type $atributos   = "nome, idade, sexo"
     * @param type $valores     = "rafael, 21, M"
     * @param type $argumentos  = "sis" 
     * @return int = json formated
     */
    public function create($atributos, $valores, $argumentos) {
        $qtdValores = $this->funcoes->getInterrogacoes($atributos);

        $valores = explode(",", $valores);

        $sql = "INSERT INTO $this->tabela ($atributos) VALUES ($qtdValores)";
        if ($stmt = $this->con->prepare($sql)) {
            $stmt->bind_param($argumentos, ...$valores);
            $stmt->execute();

            if ($stmt->affected_rows >= 1) {
                $json['flag'] = $stmt->insert_id;
            } else {
                $json['flag'] = 0;
            }
        } else {
            $json['flag'] = 0;
        }

        $stmt->close();
        $this->con->close();

        fwrite($this->myfile , "   SQL: ". $sql ."\n");
        return $json;
    }

    
    /*public function read($atributos, $condicao, $ordenacao, $argumentos) {
        if (empty($atributos)) {
            $atributos = "*";
        }

        $execute = FALSE;
        if (empty($argumentos) && empty($condicao)) {
            $sql = "SELECT $atributos FROM $this->tabela $ordenacao";
            $stmt = $this->con->prepare($sql);
            $execute = TRUE;
        } else if (!empty($condicao)) {
            $pos = strpos($condicao, "->");
            if ($pos === 0) { 
                $condicao = str_replace("->", "", $condicao);
                $func = new Funcao();
                $argumentos = $func->getInArgs($condicao);
                $values = $func->getInValues($condicao);
                $condicao = $func->getInSql($condicao);
                
                $sql = "SELECT $atributos FROM $this->tabela $ordenacao WHERE $condicao";       
                //die($sql);
            } else {
                $func = new Funcao();
                $values = $func->prepareCondReadValues($condicao);
                $condicao = $func->prepareCondReadInterrogacao($condicao);
                $condicao = " WHERE " . $condicao;

                $sql = "SELECT $atributos FROM $this->tabela $condicao $ordenacao";
            }            
            
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param($argumentos, ...$values);
            //die($stmt->errno);
            $execute = TRUE;
            
//            $codigo = 1;
//            $nome = "Sabrina";
//            $sql = "SELECT $atributos FROM $this->tabela $condicao $ordenacao";
//            $stmt = $this->con->prepare("SELECT nome FROM adm WHERE codigo = ? OR nome = ?");
//            $stmt->bind_param("ss", $codigo, $nome);
        }

        if ($execute === TRUE) {
            $stmt->execute();

            $meta = $stmt->result_metadata();

            while ($field = $meta->fetch_field()) {
                $params[] = &$row[$field->name];
            }

            call_user_func_array(array($stmt, 'bind_result'), $params);

            while ($stmt->fetch()) {
                foreach ($row as $key => $val) {
                    $c[$key] = $val;
                }
                $result[] = $c;
            }

            $stmt->close();
            $this->con->close();
        }

        if ($execute === FALSE) {
            $result['flag'] = "ERROR_PREPARE";
        } else if (empty($result)) {
            $result['flag'] = "SEM_RESULT";
        }

        fwrite($this->myfile , "   SQL: ". $sql ."\n");
        return $result;
    }*/
    
    
    /**
     * Le um registro do banco de dados e retorna um json;
     * @param string $atributos = "nome, sexo": É passado as linhas que se deseja obter com a consulta.
     * @param type $condicao = ex: "cod = 1, nome = rafael" ou "" (vazio) para listar tudo;
     * @param type $ordenação = "ORDER BY nome DESC" : Qual a ordenação que a consulta terá.
     * @return type : retorna um jsonArray;
     */
    public function read($atributos, $condicao, $ordenacao, $argumentos) {
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
            $condicao = " WHERE " . $condicao;

            $sql = "SELECT $atributos FROM $this->tabela $condicao $ordenacao";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param($argumentos, ...$values);
            $execute = TRUE;
//            $codigo = 1;
//            $nome = "Sabrina";
//            $sql = "SELECT $atributos FROM $this->tabela $condicao $ordenacao";
//            $stmt = $this->con->prepare("SELECT nome FROM adm WHERE codigo = ? OR nome = ?");
//            $stmt->bind_param("ss", $codigo, $nome);
        }

        if ($execute === TRUE) {
            $stmt->execute();

            $meta = $stmt->result_metadata();

            while ($field = $meta->fetch_field()) {
                $params[] = &$row[$field->name];
            }

            call_user_func_array(array($stmt, 'bind_result'), $params);

            while ($stmt->fetch()) {
                foreach ($row as $key => $val) {
                    $c[$key] = $val;
                }
                $result[] = $c;
            }

            $stmt->close();
            $this->con->close();
        }

        if ($execute === FALSE) {
            $result['flag'] = "ERROR_PREPARE";
        } else if (empty($result)) {
            $result['flag'] = "SEM_RESULT";
        }

        fwrite($this->myfile , "   SQL: ". $sql ."\n");
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
    public function update($atributos, $valores, $argumentos, $condicao) {
        $atributos = $this->funcoes->setUpdate($atributos, $valores);
        $condicao = explode("=", $condicao);

        $valores .= ", $condicao[1]";
        $valores = explode(",", $valores);

        $sql = "UPDATE $this->tabela SET $atributos WHERE $condicao[0] = ?";
        if ($stmt = $this->con->prepare($sql)) {
            $stmt->bind_param($argumentos, ...$valores);
            $stmt->execute();

            if ($stmt->affected_rows >= 1) {
                $json['flag'] = $stmt->affected_rows;
            } else {
                $json['flag'] = 0;
            }
        } else {
            //echo $this->con->error;
            $json['flag'] = 0;
        }

        $stmt->close();
        $this->con->close();

        fwrite($this->myfile , "   SQL: ". $sql ."\n");
        return $json;
    }

    /**
     * Exclui uma linha do banco de dados.
     * @param type $atributo = "codigo" : A chave da linha que sera excluida
     * @param type $valor = "21"        : O valor da chave que sera excluida.
     * @param type $argumentos = "i"    : argumen da chave que sera excluida.
     * @return int : Retorna um json com a quantidade de linhas afetadas.
     */
    public function delete($atributo, $valor, $argumentos) {
        $sql = "DELETE FROM $this->tabela WHERE $atributo = ?";
        if ($stmt = $this->con->prepare($sql)) {
            $stmt->bind_param($argumentos, $valor);
            $stmt->execute();

            if ($stmt->affected_rows >= 1) {
                $json['flag'] = $stmt->affected_rows;
            } else {
                $json['flag'] = 0;
            }
        } else {
            $json['flag'] = 0;
        }

        $stmt->close();
        $this->con->close();

        fwrite($this->myfile , "   SQL: ". $sql ."\n");
        return $json;
    }    

}
