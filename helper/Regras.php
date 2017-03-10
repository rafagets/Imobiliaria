<?php

class Metodos {

    function hashSenha($senha) {
        $senha = hash("sha512", $senha);
        return $senha;
    }
    
    function autenticacao($email, $senha) {
        // toda a regra aqui
        
        return TRUE;
    }

}
