<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Tela obrigatoria para trocar a senha inicial do usuario autenticado.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/includes/funcoes.php';

// VALIDAÇÃO - Confirma que existe uma sessao autenticada antes de continuar.
exigirLogin('');

// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';
// PROCESSAMENTO - Variavel $usuario guarda estado ou dados usados nas proximas etapas.
$usuario = usuarioLogado();

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PROCESSAMENTO - Variavel $senha guarda estado ou dados usados nas proximas etapas.
    $senha = post('senha');
    // PROCESSAMENTO - Variavel $confirmacao guarda estado ou dados usados nas proximas etapas.
    $confirmacao = post('confirmacao');

    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (strlen($senha) < 4) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = 'A senha deve ter pelo menos 4 caracteres.';
    } elseif ($senha !== $confirmacao) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = 'A confirmacao da senha nao confere.';
    } else {
        // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
        executar(
            'UPDATE usuarios SET senha_hash = :senha_hash, ultimo_login = NOW() WHERE id_usuario = :id_usuario',
            [
                ':senha_hash' => hashSenha($senha),
                ':id_usuario' => $usuario['id_usuario'],
            ]
        );

        $_SESSION['usuario']['trocar_senha'] = false;
        // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
        registrarLog('Alterou senha');
        // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
        flash('success', 'Senha alterada com sucesso.');
        // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
        redirecionar('dashboard.php');
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Alterar senha | Flow Academy</title>
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="auth-shell">
  <main class="auth-card">
    <a class="brand" href="dashboard.php">
      <img class="brand-logo" src="assets/img/logos/logo-flow-academy-gold.jpg" alt="Logo Flow Academy">
    </a>
    <h1 class="auth-title">Alterar senha</h1>
    <p class="auth-subtitle">No primeiro acesso, cadastre uma nova senha.</p>

<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
    <?php if ($erro !== ''): ?>
      <div class="alert danger"><span class="alert-marker"></span><div><strong>Aviso</strong><span class="muted"><?= e($erro) ?></span></div></div>
    <?php endif; ?>

<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
    <form class="stack" method="post">
      <label class="field">
        <span>Nova senha</span>
        <input class="control" type="password" name="senha" required>
      </label>
      <label class="field">
        <span>Confirmar senha</span>
        <input class="control" type="password" name="confirmacao" required>
      </label>
      <button class="btn primary" type="submit">Salvar senha</button>
    </form>
  </main>
</body>
</html>

<!-- FIM - Final do arquivo documentado para estudo. -->