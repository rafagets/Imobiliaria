<?php

class Tabelas {

    private $tabs;

    public function __construct() {
        // Em $tabs devera conter todas as tabelas que serão manipiuladas pelo sistema.
        // Elas devem ser separadas por ", " (virgula e um espaço);
        $tabs = "adm, paciente, pagamento, tipopaciente";
        $this->tabs = explode(", ", $tabs);
    }

    /**
     * Verifica se a tabela passada por parametro existe no banco de dados.
     * @param type $tabela = "usuario" : Nome da tabela que será verificada a existencia
     * @return boolean : retorna um true ou false
     */
    public function isExist($tabela) {
        foreach ($this->tabs as &$val) {
            if ($val === $tabela) {
                return TRUE;
            }
        }
        return FALSE;
    }

}
