<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Cadastra usuario de coordenacao e registra a acao no log.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Cadastro de Coordenacao';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'cadastro_coordenacao';
// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';
// PROCESSAMENTO - Variavel $perfilAlvo guarda estado ou dados usados nas proximas etapas.
$perfilAlvo = 'coordenacao';

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('acao') === 'salvar') {
    // TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
    try {
        // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
        executar(
            'INSERT INTO usuarios (nome, email, senha_hash, perfil, status) VALUES (:nome, :email, :senha_hash, :perfil, :status)',
            [
                ':nome' => post('nome'),
                ':email' => post('email'),
                ':senha_hash' => hashSenha(post('senha', '123456')),
                ':perfil' => $perfilAlvo,
                ':status' => post('status', 'ativo'),
            ]
        );
        // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
        registrarLog('Cadastrou usuario de coordenacao');
        // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
        flash('success', 'Usuario cadastrado com sucesso.');
        // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
        redirecionar('cadastro_coordenacao.php');
    } catch (Throwable $erroSql) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = $erroSql->getMessage();
    }
}

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$usuarios = buscarTodos('SELECT * FROM usuarios WHERE perfil = :perfil ORDER BY nome', [':perfil' => $perfilAlvo]);

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading"><div><span class="eyebrow">Admin</span><h1>Cadastro de Coordenacao</h1><p>Cria o usuario de acesso para funcionarios da coordenacao.</p></div></section>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
<?php if ($erro): ?><div class="alert danger"><span class="alert-marker"></span><div><strong>Erro</strong><span class="muted"><?= e($erro) ?></span></div></div><?php endif; ?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Dados do usuario</h2></div>
  <div class="panel-body">
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
    <form class="form-grid" method="post">
      <input type="hidden" name="acao" value="salvar">
      <label class="field"><span>Nome completo</span><input class="control" name="nome" required></label>
      <label class="field"><span>E-mail</span><input class="control" type="email" name="email" required></label>
      <label class="field"><span>Senha inicial</span><input class="control" name="senha" value="123456"></label>
      <label class="field"><span>Status de acesso</span>
        <select class="select" name="status"><option value="ativo">Ativo</option><option value="inativo">Inativo</option></select>
      </label>
      <div class="actions span-2"><button class="btn primary" type="submit">Cadastrar usuario</button><a class="btn ghost" href="../dashboard.php">Voltar</a></div>
    </form>
  </div>
</section>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Funcionarios da coordenacao cadastrados</h2></div>
  <div class="table-wrap">
<!-- RETORNO - Tabela apresenta ao usuario os dados consultados no banco. -->
    <table id="tabela-principal">
      <thead><tr><th>Nome</th><th>E-mail</th><th>Status</th><th>Ultimo login</th><th>Cadastro</th></tr></thead>
      <tbody>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
        <?php if (empty($usuarios)): ?><tr><td colspan="5">Nenhum funcionario cadastrado.</td></tr><?php endif; ?>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?= e($u['nome']) ?></td><td><?= e($u['email']) ?></td><td><?= badge($u['status']) ?></td>
            <td><?= $u['ultimo_login'] ? e(dataBr($u['ultimo_login'])) : '-' ?></td>
            <td>-</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->