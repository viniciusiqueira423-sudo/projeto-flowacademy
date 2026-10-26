<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Gerencia usuarios do sistema, inclusive status e senha temporaria.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Usuarios';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'usuarios';
// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
    try {
        // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
        if (post('acao') === 'salvar') {
            // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
            executar(
                'INSERT INTO usuarios (nome, email, senha_hash, perfil, status) VALUES (:nome, :email, :senha_hash, :perfil, :status)',
                [
                    ':nome' => post('nome'),
                    ':email' => post('email'),
                    ':senha_hash' => hashSenha(post('senha', '123456')),
                    ':perfil' => post('perfil'),
                    ':status' => post('status', 'ativo'),
                ]
            );
            // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
            registrarLog('Cadastrou usuario');
            // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
            flash('success', 'Usuario cadastrado com sucesso.');
            // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
            redirecionar('usuarios.php');
        }

        // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
        if (post('acao') === 'status') {
            // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
            executar(
                'UPDATE usuarios SET status = :status WHERE id_usuario = :id_usuario',
                [':status' => post('status'), ':id_usuario' => (int) post('id_usuario')]
            );
            // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
            registrarLog('Atualizou status de usuario');
            // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
            flash('success', 'Usuario atualizado com sucesso.');
            // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
            redirecionar('usuarios.php');
        }
    } catch (Throwable $erroSql) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = $erroSql->getMessage();
    }
}

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$usuarios = buscarTodos('SELECT * FROM usuarios ORDER BY nome');

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading"><div><span class="eyebrow">Admin</span><h1>Usuarios</h1><p>Cadastro simples de usuarios administrativos, coordenacao e admin.</p></div></section>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
<?php if ($erro): ?><div class="alert danger"><span class="alert-marker"></span><div><strong>Erro</strong><span class="muted"><?= e($erro) ?></span></div></div><?php endif; ?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Novo usuario</h2></div>
  <div class="panel-body">
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
    <form class="form-grid" method="post">
      <input type="hidden" name="acao" value="salvar">
      <label class="field"><span>Nome</span><input class="control" name="nome" required></label>
      <label class="field"><span>E-mail</span><input class="control" type="email" name="email" required></label>
      <label class="field"><span>Senha inicial</span><input class="control" name="senha" value="123456"></label>
      <label class="field"><span>Perfil</span><select class="select" name="perfil"><option value="admin">Admin</option><option value="coordenacao">Coordenacao</option><option value="administrativo">Administrativo</option></select></label>
      <label class="field"><span>Status</span><select class="select" name="status"><option value="ativo">Ativo</option><option value="inativo">Inativo</option></select></label>
      <div class="actions"><button class="btn primary" type="submit">Salvar</button></div>
    </form>
  </div>
</section>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Lista de usuarios</h2></div>
  <div class="table-wrap"><table id="tabela-principal"><thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Status</th><th>Ultimo login</th><th>Alterar</th></tr></thead><tbody>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
    <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?= e($u['nome']) ?></td><td><?= e($u['email']) ?></td><td><?= e(textoStatus($u['perfil'])) ?></td><td><?= badge($u['status']) ?></td><td><?= e($u['ultimo_login'] ?? '-') ?></td>
        <td><form method="post" class="actions"><input type="hidden" name="acao" value="status"><input type="hidden" name="id_usuario" value="<?= e($u['id_usuario']) ?>"><select class="select" name="status"><option value="ativo">Ativo</option><option value="inativo">Inativo</option></select><button class="btn ghost" type="submit">Salvar</button></form></td>
      </tr>
    <?php endforeach; ?>
  </tbody></table></div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->