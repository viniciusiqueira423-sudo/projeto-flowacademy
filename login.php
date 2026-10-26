<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Tela de autenticacao que valida e-mail/senha e cria a sessao do usuario.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/includes/funcoes.php';

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if (estaLogado()) {
    // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
    redirecionar('dashboard.php');
}

// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';
// PROCESSAMENTO - Variavel $email guarda estado ou dados usados nas proximas etapas.
$email = '';

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PROCESSAMENTO - Variavel $email guarda estado ou dados usados nas proximas etapas.
    $email = post('email');
    // PROCESSAMENTO - Variavel $senha guarda estado ou dados usados nas proximas etapas.
    $senha = post('senha');

    // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
    $usuario = buscarUm(
        'SELECT * FROM usuarios WHERE email = :email AND status = "ativo" LIMIT 1',
        [':email' => $email]
    );

    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if (!$usuario || !senhaCorreta($senha, $usuario['senha_hash'])) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = 'E-mail ou senha incorretos.';
    } else {
        $_SESSION['usuario'] = [
            'id_usuario' => (int) $usuario['id_usuario'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'perfil' => $usuario['perfil'],
            'trocar_senha' => $usuario['ultimo_login'] === null,
        ];

        // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
        if ($usuario['ultimo_login'] === null) {
            // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
            registrarLog('Login realizado');
            // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
            redirecionar('alterar_senha.php');
        }

        // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
        executar('UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = :id', [
            ':id' => $usuario['id_usuario'],
        ]);

        // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
        registrarLog('Login realizado');
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
  <title>Login | Flow Academy</title>
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="auth-shell">
  <main class="auth-card">
    <a class="brand" href="index.php">
      <img class="brand-logo" src="assets/img/logos/logo-flow-academy-gold.jpg" alt="Logo Flow Academy">
    </a>
    <h1 class="auth-title">Acesso institucional</h1>
    <p class="auth-subtitle">Entre com um usuario cadastrado na tabela usuarios do banco flow_academy.</p>

<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
    <?php if ($erro !== ''): ?>
      <div class="alert danger">
        <span class="alert-marker"></span>
        <div><strong>Erro de autenticacao</strong><span class="muted"><?= e($erro) ?></span></div>
      </div>
    <?php endif; ?>

<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
    <form class="stack" method="post">
      <label class="field">
        <span>E-mail</span>
        <input class="control" type="email" name="email" placeholder="seu.email@flowacademy.com" value="<?= e($email) ?>" required>
      </label>
      <label class="field">
        <span>Senha</span>
        <span class="password-wrap">
          <input class="control" id="login-password" type="password" name="senha" placeholder="Digite sua senha" required>
          <button class="btn ghost password-action" type="button" data-password-toggle="#login-password">Mostrar</button>
        </span>
      </label>
      <button class="btn primary" type="submit">Entrar</button>
    </form>
  </main>

  <div class="toast" data-toast-root></div>
  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>

<!-- FIM - Final do arquivo documentado para estudo. -->