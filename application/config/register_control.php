<?php

// Controle global de acesso a registros no banco de dados
// Criar array, no modelo tabela.campo => valor_para_controle
// Exemplo: Para por padrão exibir apenas produtos ativos:
// $register_control = array('produto.ativo' => 1);
// Essa array será inserida automaticamente nos métodos get, get_array, get_all, get_like, get_or, update e delete

$register_control = FALSE;