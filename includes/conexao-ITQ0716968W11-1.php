<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Centraliza a configuracao da conexao PDO com o banco MySQL.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// PROCESSAMENTO - Variavel $host guarda estado ou dados usados nas proximas etapas.
$host = '10.91.47.67';
// PROCESSAMENTO - Variavel $banco guarda estado ou dados usados nas proximas etapas.
$banco = 'flow_academy';
// PROCESSAMENTO - Variavel $usuario guarda estado ou dados usados nas proximas etapas.
$usuario = 'root';
// PROCESSAMENTO - Variavel $senha guarda estado ou dados usados nas proximas etapas.
$senha = 'P@ssw0rd';

// TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
try {
    // PROCESSAMENTO - Variavel $pdo guarda estado ou dados usados nas proximas etapas.
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $erro) {
    die('Erro ao conectar no banco de dados: ' . $erro->getMessage());
}

// FIM - Final do arquivo documentado para estudo.