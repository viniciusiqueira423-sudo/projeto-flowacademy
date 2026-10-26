<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Encerra a sessao atual e redireciona o usuario para a tela de login.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/includes/funcoes.php';

// PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
registrarLog('Logout realizado');
// PROCESSAMENTO - Variavel $_SESSION guarda estado ou dados usados nas proximas etapas.
$_SESSION = [];
session_destroy();

// RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
redirecionar('login.php');

// FIM - Final do arquivo documentado para estudo.