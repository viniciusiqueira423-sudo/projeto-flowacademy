<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Reune funcoes auxiliares de seguranca, banco, sessao, formatacao e feedback.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/conexao.php';

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// INÍCIO - Funcao e: Escapa textos antes de exibir no HTML, evitando injecao de codigo no navegador.
function e($valor)
{
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

// INÍCIO - Funcao post: Le um campo enviado por POST e aplica trim para padronizar a entrada.
function post($campo, $padrao = '')
{
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return trim((string) ($_POST[$campo] ?? $padrao));
}

// INÍCIO - Funcao getValor: Le um parametro de GET e aplica trim para padronizar filtros/identificadores.
function getValor($campo, $padrao = '')
{
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return trim((string) ($_GET[$campo] ?? $padrao));
}

// INÍCIO - Funcao buscarUm: Executa uma consulta preparada e retorna apenas o primeiro registro encontrado.
function buscarUm($sql, $parametros = [])
{
    global $pdo;
    // PROCESSAMENTO - Variavel $stmt guarda estado ou dados usados nas proximas etapas.
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    // PROCESSAMENTO - Variavel $linha guarda estado ou dados usados nas proximas etapas.
    $linha = $stmt->fetch();
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return $linha ?: null;
}

// INÍCIO - Funcao buscarTodos: Executa uma consulta preparada e retorna todos os registros encontrados.
function buscarTodos($sql, $parametros = [])
{
    global $pdo;
    // PROCESSAMENTO - Variavel $stmt guarda estado ou dados usados nas proximas etapas.
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return $stmt->fetchAll();
}

// INÍCIO - Funcao executar: Executa comandos de escrita ou atualizacao no banco usando parametros preparados.
function executar($sql, $parametros = [])
{
    global $pdo;
    // PROCESSAMENTO - Variavel $stmt guarda estado ou dados usados nas proximas etapas.
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return $stmt;
}

// INÍCIO - Funcao redirecionar: Envia cabecalho HTTP Location e encerra a execucao para evitar processamento extra.
function redirecionar($caminho)
{
    header('Location: ' . $caminho);
    exit;
}

// INÍCIO - Funcao flash: Guarda uma mensagem temporaria na sessao para exibicao apos redirecionamento.
function flash($tipo, $mensagem)
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

// INÍCIO - Funcao pegarFlash: Le e remove a mensagem temporaria, garantindo exibicao unica.
function pegarFlash()
{
    // PROCESSAMENTO - Variavel $flash guarda estado ou dados usados nas proximas etapas.
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return $flash;
}

// INÍCIO - Funcao usuarioLogado: Retorna os dados do usuario autenticado armazenados na sessao.
function usuarioLogado()
{
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return $_SESSION['usuario'] ?? null;
}

// INÍCIO - Funcao estaLogado: Confirma se existe usuario autenticado na sessao atual.
function estaLogado()
{
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return usuarioLogado() !== null;
}

// INÍCIO - Funcao exigirLogin: Protege paginas privadas e força troca de senha inicial quando necessario.
function exigirLogin($base = '')
{
    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (!estaLogado()) {
        // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
        flash('danger', 'Faca login para acessar o sistema.');
        // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
        redirecionar($base . 'login.php');
    }

    // PROCESSAMENTO - Variavel $arquivoAtual guarda estado ou dados usados nas proximas etapas.
    $arquivoAtual = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (!empty($_SESSION['usuario']['trocar_senha']) && $arquivoAtual !== 'alterar_senha.php') {
        // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
        redirecionar($base . 'alterar_senha.php');
    }
}

// INÍCIO - Funcao exigirPerfil: Valida se o usuario autenticado possui um dos perfis permitidos para a pagina.
function exigirPerfil($perfisPermitidos, $base = '')
{
    // VALIDAÇÃO - Confirma que existe uma sessao autenticada antes de continuar.
    exigirLogin($base);
    // PROCESSAMENTO - Variavel $usuario guarda estado ou dados usados nas proximas etapas.
    $usuario = usuarioLogado();

    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (!in_array($usuario['perfil'], $perfisPermitidos, true)) {
        // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
        flash('danger', 'Seu usuario nao tem permissao para acessar esta pagina.');
        // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
        redirecionar($base . 'dashboard.php');
    }
}

// Permite que o Admin "assuma" a visualizacao de outro perfil (Coordenacao ou
// Administrativo) apenas para fins de menu/dashboard, sem alterar a sessao real
// nem as permissoes de acesso. Para qualquer outro perfil, retorna o proprio perfil.
// INÍCIO - Funcao perfilAtivo: Define qual perfil sera usado para menu/dashboard, preservando as permissoes reais.
function perfilAtivo()
{
    // PROCESSAMENTO - Variavel $usuario guarda estado ou dados usados nas proximas etapas.
    $usuario = usuarioLogado();
    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (!$usuario) {
        // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
        return null;
    }

    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if ($usuario['perfil'] !== 'admin') {
        // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
        return $usuario['perfil'];
    }

    // PROCESSAMENTO - Variavel $permitidos guarda estado ou dados usados nas proximas etapas.
    $permitidos = ['admin', 'coordenacao', 'administrativo'];
    // PROCESSAMENTO - Variavel $solicitado guarda estado ou dados usados nas proximas etapas.
    $solicitado = getValor('ver');
    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (in_array($solicitado, $permitidos, true)) {
        $_SESSION['ver_perfil'] = $solicitado;
    }

    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return $_SESSION['ver_perfil'] ?? 'admin';
}

// INÍCIO - Funcao paginaInicialPorPerfil: Centraliza o destino inicial apos login para cada perfil.
function paginaInicialPorPerfil($perfil)
{
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return 'dashboard.php';
}

// INÍCIO - Funcao hashSenha: Gera hash SHA-256 da senha informada, seguindo o padrao atual do projeto.
function hashSenha($senha)
{
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return hash('sha256', (string) $senha);
}

// INÍCIO - Funcao senhaCorreta: Compara senha digitada com hash salvo usando comparacao segura.
function senhaCorreta($senhaDigitada, $hashBanco)
{
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return hash_equals((string) $hashBanco, hashSenha($senhaDigitada));
}

// INÍCIO - Funcao registrarLog: Registra a acao do usuario no banco sem interromper a operacao principal se falhar.
function registrarLog($acao)
{
    // PROCESSAMENTO - Variavel $usuario guarda estado ou dados usados nas proximas etapas.
    $usuario = usuarioLogado();

    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (!$usuario) {
        // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
        return;
    }

    // TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
    try {
        // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
        executar(
            'INSERT INTO logs (id_usuario, acao, ip) VALUES (:id_usuario, :acao, :ip)',
            [
                ':id_usuario' => $usuario['id_usuario'],
                ':acao' => $acao,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]
        );
    } catch (Throwable $erro) {
        // O log nao deve impedir a acao principal.
    }
}

// INÍCIO - Funcao textoStatus: Traduz codigos internos de status para rotulos legiveis.
function textoStatus($status)
{
    // PROCESSAMENTO - Variavel $textos guarda estado ou dados usados nas proximas etapas.
    $textos = [
        'ativo' => 'Ativo',
        'inativo' => 'Inativo',
        'regular' => 'Regular',
        'trancado' => 'Trancado',
        'jubilado' => 'Jubilado',
        'evadido' => 'Evadido',
        'ativa' => 'Ativa',
        'cancelada' => 'Cancelada',
        'concluida' => 'Concluida',
        'encerrada' => 'Encerrada',
        'pendente' => 'Pendente',
        'pago' => 'Pago',
        'atrasado' => 'Atrasado',
        'cancelado' => 'Cancelado',
        'aprovado' => 'Aprovado',
        'reprovado' => 'Reprovado',
        'em_andamento' => 'Em andamento',
        'manha' => 'Manha',
        'tarde' => 'Tarde',
        'noite' => 'Noite',
    ];

    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return $textos[$status] ?? ucfirst((string) $status);
}

// INÍCIO - Funcao classeBadge: Converte o status em classe visual usada pelos badges.
function classeBadge($status)
{
    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (in_array($status, ['ativo', 'regular', 'ativa', 'pago', 'aprovado'], true)) {
        // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
        return 'success';
    }

    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (in_array($status, ['pendente', 'em_andamento'], true)) {
        // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
        return 'warning';
    }

    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (in_array($status, ['inativo', 'cancelada', 'cancelado', 'trancado', 'jubilado', 'evadido', 'reprovado', 'atrasado'], true)) {
        // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
        return 'danger';
    }

    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return 'info';
}

// INÍCIO - Funcao badge: Monta o HTML do badge ja escapando o texto do status.
function badge($status)
{
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return '<span class="badge ' . classeBadge($status) . '">' . e(textoStatus($status)) . '</span>';
}

// INÍCIO - Funcao dataBr: Formata datas no padrao brasileiro ou retorna marcador quando vazias.
function dataBr($data)
{
    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (!$data) {
        // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
        return '-';
    }

    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return date('d/m/Y', strtotime($data));
}

// INÍCIO - Funcao dinheiro: Formata valores monetarios em reais.
function dinheiro($valor)
{
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

// INÍCIO - Funcao contar: Executa uma consulta de contagem e retorna o total como inteiro.
function contar($sql, $parametros = [])
{
    // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
    $linha = buscarUm($sql, $parametros);
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return (int) ($linha['total'] ?? 0);
}

// INÍCIO - Funcao atualizarPagamentosAtrasados: Atualiza pagamentos pendentes vencidos para atrasado.
function atualizarPagamentosAtrasados()
{
    // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
    executar(
        'UPDATE pagamentos
         SET status = "atrasado"
         WHERE vencimento < CURDATE()
           AND status = "pendente"'
    );
}

// INÍCIO - Funcao iniciais: Calcula iniciais do nome para avatar textual.
function iniciais($nome)
{
    // PROCESSAMENTO - Variavel $partes guarda estado ou dados usados nas proximas etapas.
    $partes = preg_split('/\s+/', trim((string) $nome));
    // PROCESSAMENTO - Variavel $primeira guarda estado ou dados usados nas proximas etapas.
    $primeira = $partes[0][0] ?? 'F';
    // PROCESSAMENTO - Variavel $segunda guarda estado ou dados usados nas proximas etapas.
    $segunda = $partes[1][0] ?? '';
    // RETORNO - Devolve o resultado calculado para quem chamou esta funcao/bloco.
    return strtoupper($primeira . $segunda);
}

// FIM - Final do arquivo documentado para estudo.