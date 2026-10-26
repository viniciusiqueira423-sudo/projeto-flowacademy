<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Cadastra professor, usuario de acesso e dados profissionais.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Professores';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'professores';
// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';
// PROCESSAMENTO - Variavel $editando guarda estado ou dados usados nas proximas etapas.
$editando = null;

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
    try {
        // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
        if (post('acao') === 'salvar') {
            // PROCESSAMENTO - Variavel $idProfessor guarda estado ou dados usados nas proximas etapas.
            $idProfessor = (int) post('id_professor');

            // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
            if ($idProfessor > 0) {
                // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
                $professorAtual = buscarUm('SELECT id_usuario FROM professores WHERE id_professor = :id', [':id' => $idProfessor]);
                // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
                executar(
                    'UPDATE usuarios SET nome = :nome, email = :email, status = :status WHERE id_usuario = :id_usuario',
                    [
                        ':nome' => post('nome'),
                        ':email' => post('email'),
                        ':status' => post('status', 'ativo'),
                        ':id_usuario' => $professorAtual['id_usuario'],
                    ]
                );
                // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
                executar(
                    'UPDATE professores SET cpf = :cpf, especialidade = :especialidade WHERE id_professor = :id_professor',
                    [
                        ':cpf' => post('cpf'),
                        ':especialidade' => post('especialidade'),
                        ':id_professor' => $idProfessor,
                    ]
                );
                // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
                flash('success', 'Professor atualizado com sucesso.');
            } else {
                // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
                executar(
                    'INSERT INTO usuarios (nome, email, senha_hash, perfil, status) VALUES (:nome, :email, :senha_hash, "professor", "ativo")',
                    [':nome' => post('nome'), ':email' => post('email'), ':senha_hash' => hashSenha(post('senha', '123456'))]
                );
                // PROCESSAMENTO - Variavel $idUsuario guarda estado ou dados usados nas proximas etapas.
                $idUsuario = $pdo->lastInsertId();
                // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
                executar(
                    'INSERT INTO professores (id_usuario, cpf, especialidade) VALUES (:id_usuario, :cpf, :especialidade)',
                    [':id_usuario' => $idUsuario, ':cpf' => post('cpf'), ':especialidade' => post('especialidade')]
                );
                // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
                flash('success', 'Professor cadastrado com sucesso.');
            }

            // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
            registrarLog('Salvou professor');
            // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
            redirecionar('professores.php');
        }

        // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
        if (post('acao') === 'excluir') {
            // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
            $professor = buscarUm('SELECT id_usuario FROM professores WHERE id_professor = :id', [':id' => (int) post('id_professor')]);
            // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
            if ($professor) {
                // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
                executar('DELETE FROM usuarios WHERE id_usuario = :id_usuario', [':id_usuario' => $professor['id_usuario']]);
                // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
                registrarLog('Excluiu professor');
                // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
                flash('success', 'Professor excluido com sucesso.');
            }
            // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
            redirecionar('professores.php');
        }
    } catch (Throwable $erroSql) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = $erroSql->getMessage();
    }
}

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if (getValor('editar') !== '') {
    // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
    $editando = buscarUm(
        'SELECT p.*, u.nome, u.email, u.status
         FROM professores p
         JOIN usuarios u ON u.id_usuario = p.id_usuario
         WHERE p.id_professor = :id',
        [':id' => (int) getValor('editar')]
    );
}

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$professores = buscarTodos(
    'SELECT p.*, u.nome, u.email, u.status
     FROM professores p
     JOIN usuarios u ON u.id_usuario = p.id_usuario
     ORDER BY u.nome'
);

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading">
  <div><span class="eyebrow">Admin</span><h1>Professores</h1><p>Cadastro direto em usuarios e professores.</p></div>
</section>

<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
<?php if ($erro): ?><div class="alert danger"><span class="alert-marker"></span><div><strong>Erro</strong><span class="muted"><?= e($erro) ?></span></div></div><?php endif; ?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2><?= $editando ? 'Editar professor' : 'Cadastrar professor' ?></h2></div>
  <div class="panel-body">
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
    <form class="form-grid" method="post">
      <input type="hidden" name="acao" value="salvar">
      <input type="hidden" name="id_professor" value="<?= e($editando['id_professor'] ?? '') ?>">
      <label class="field"><span>Nome</span><input class="control" name="nome" value="<?= e($editando['nome'] ?? '') ?>" required></label>
      <label class="field"><span>E-mail</span><input class="control" type="email" name="email" value="<?= e($editando['email'] ?? '') ?>" required></label>
      <label class="field"><span>CPF</span><input class="control" name="cpf" value="<?= e($editando['cpf'] ?? '') ?>" required></label>
      <label class="field"><span>Especialidade</span><input class="control" name="especialidade" value="<?= e($editando['especialidade'] ?? '') ?>"></label>
      <label class="field"><span>Status</span><select class="select" name="status"><option value="ativo">Ativo</option><option value="inativo" <?= ($editando['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option></select></label>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
      <?php if (!$editando): ?><label class="field"><span>Senha inicial</span><input class="control" name="senha" value="123456"></label><?php endif; ?>
      <div class="actions span-2"><button class="btn primary" type="submit">Salvar</button><a class="btn ghost" href="professores.php">Limpar</a></div>
    </form>
  </div>
</section>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Lista de professores</h2></div>
  <div class="table-wrap">
<!-- RETORNO - Tabela apresenta ao usuario os dados consultados no banco. -->
    <table id="tabela-principal">
      <thead><tr><th>Nome</th><th>E-mail</th><th>CPF</th><th>Especialidade</th><th>Status</th><th>Acoes</th></tr></thead>
      <tbody>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
        <?php foreach ($professores as $professor): ?>
          <tr>
            <td><?= e($professor['nome']) ?></td><td><?= e($professor['email']) ?></td><td><?= e($professor['cpf']) ?></td><td><?= e($professor['especialidade']) ?></td><td><?= badge($professor['status']) ?></td>
            <td class="table-actions">
              <a class="btn ghost" href="professores.php?editar=<?= e($professor['id_professor']) ?>">Editar</a>
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
              <form method="post" onsubmit="return confirm('Excluir este professor?')"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id_professor" value="<?= e($professor['id_professor']) ?>"><button class="btn danger" type="submit">Excluir</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->