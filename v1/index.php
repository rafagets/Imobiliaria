<?php

/**
 * Erros que podem ocorrer:
 * 'ACCESS_DENIED'  : Nehum parametro foi passado ao servidor;
 * 'LOGIN_ERROR'    : O email e senha são ivalidos;
 * 'INVALID_TABLE'  : A tabela solicitada é invalida;
 * 'CONN_FAILED'    : Falha ao conectar ao banco;
 * 'FALTA_ARGS'     : Falta argumentos para manipular;
 * 'OPC_INVALIDA'   : A opção enviada é invalida;
 * 'SEM_RESULT      : A consulta não retornou nehum objeto.
 * 'ERROR_PREPARE'   : Ocorreu algum erro na preparação da query
 */
//error_reporting(0);
require_once '../helper/crud.php';

$myfile = fopen("log.txt", "a") or die("Falha no log!");

$email = "";
$senha = "hhf";

$acao = filter_input(INPUT_POST, 'acao');
$tabela = filter_input(INPUT_POST, 'tabela');
$atributos = filter_input(INPUT_POST, 'atributos');
$valores = filter_input(INPUT_POST, 'valores');
$argumentos = filter_input(INPUT_POST, 'argumentos');
$condicao = filter_input(INPUT_POST, 'condicao');
$ordenacao = filter_input(INPUT_POST, 'ordenacao');

fwrite($myfile, "Nova consulta, IP_USER: ". getIp() ."{\n");
fwrite($myfile, "   Acao: ". filter_input(INPUT_POST, 'acao') ."\n");
fwrite($myfile, "   Tabela: ". filter_input(INPUT_POST, 'tabela') ."\n");
fwrite($myfile, "   Atributos: ". filter_input(INPUT_POST, 'atributos') ."\n");
fwrite($myfile, "   Valores: ". filter_input(INPUT_POST, 'valores') ."\n");
fwrite($myfile, "   Argumentos: ". filter_input(INPUT_POST, 'argumentos') ."\n");
fwrite($myfile, "   Condicao: ". filter_input(INPUT_POST, 'condicao') ."\n");
fwrite($myfile, "   Ordenacao: ". filter_input(INPUT_POST, 'ordenacao') ."\n");

if (!empty($acao) && !empty($tabela)) {

    $crud = new Crud($email, $senha, $tabela);

    switch ($acao) {
        case "C":
            if (!empty($atributos) && !empty($valores) && !empty($argumentos)) {
                $flag = $crud->prepareCreate($atributos, $valores, $argumentos);
            } else {
                $flag['flag'] = 'FALTA_ARGS';
            }

            break;
        case "R":
            $flag = $crud->prepareRead($atributos, $condicao, $ordenacao, $argumentos);
            break;

        case "U":
            if (!empty($atributos) && !empty($valores) && !empty($argumentos) && !empty($condicao)) {
                $flag = $crud->prepareUpdade($atributos, $valores, $argumentos, $condicao);
            } else {
                $flag['flag'] = 'FALTA_ARGS';
            }
            break;
        case "D":
            if (!empty($atributos) && !empty($valores) && !empty($argumentos)) {
                $flag = $crud->prepareDelete($atributos, $valores, $argumentos);
            } else {
                $flag['flag'] = 'FALTA_ARGS';
            }
            break;
        default:
            $flag['flag'] = 'OPC_INVALIDA';
            break;
    }

    echo json_encode($flag);
} else {
    echo json_encode($flag['flag'] = 'ACCESS_DENIED');
}

$json = explode("}", json_encode($flag));
$par = TRUE;
foreach ($json as $value) {
    if ($par) {
        $par = FALSE;
        fwrite($myfile, "   Flag-> " . $value . "}\n");
    } else {
        fwrite($myfile, "       -> " . $value . "}\n");
    }
}
fwrite($myfile, "}\n\n");

function getIp() {
    $http_client_ip         = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP');
    $http_x_forwarded_for   = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR');
    $remote_addr            = filter_input(INPUT_SERVER, 'REMOTE_ADDR');

    /* VERIFICO SE O IP REALMENTE EXISTE NA INTERNET */
    if (!empty($http_client_ip)) {
        $ip = $http_client_ip;
        /* VERIFICO SE O ACESSO PARTIU DE UM SERVIDOR PROXY */
    } elseif (!empty($http_x_forwarded_for)) {
        $ip = $http_x_forwarded_for;
    } else {
        /* CASO EU NÃO ENCONTRE NAS DUAS OUTRAS MANEIRAS, RECUPERO DA FORMA TRADICIONAL */
        $ip = $remote_addr;
    }

    return $ip;
}
