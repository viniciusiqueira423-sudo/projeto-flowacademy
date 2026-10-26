<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Lista pagamentos, atualiza atrasos e permite confirmar ou cancelar registros.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin', 'administrativo'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Pagamentos';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'pagamentos';

atualizarPagamentosAtrasados();

// PROCESSAMENTO - Variavel $idAlunoFiltro guarda estado ou dados usados nas proximas etapas.
$idAlunoFiltro = (int) getValor('id_aluno');
// PROCESSAMENTO - Variavel $idTurmaFiltro guarda estado ou dados usados nas proximas etapas.
$idTurmaFiltro = (int) getValor('id_turma');

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$alunos = buscarTodos(
    'SELECT a.id_aluno, a.matricula, u.nome, m.id_turma
     FROM alunos a
     JOIN usuarios u ON u.id_usuario = a.id_usuario
     LEFT JOIN matriculas m ON m.id_aluno = a.id_aluno AND m.status = "ativa"
     ORDER BY u.nome'
);
// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$turmas = buscarTodos(
    'SELECT t.id_turma, t.codigo_turma, c.nome AS curso
     FROM turmas t JOIN cursos c ON c.id_curso = t.id_curso
     ORDER BY t.codigo_turma'
);

// PROCESSAMENTO - Variavel $pagamentos guarda estado ou dados usados nas proximas etapas.
$pagamentos = [];
// PROCESSAMENTO - Variavel $alunoSelecionado guarda estado ou dados usados nas proximas etapas.
$alunoSelecionado = null;
// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if ($idAlunoFiltro > 0) {
    // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
    $alunoSelecionado = buscarUm(
        'SELECT a.*, u.nome FROM alunos a JOIN usuarios u ON u.id_usuario = a.id_usuario WHERE a.id_aluno = :id',
        [':id' => $idAlunoFiltro]
    );
    // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
    $pagamentos = buscarTodos(
        'SELECT * FROM pagamentos WHERE id_aluno = :id ORDER BY vencimento DESC',
        [':id' => $idAlunoFiltro]
    );
}

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading">
  <div><span class="eyebrow">Administrativo</span><h1>Pagamentos</h1><p>Consulta de pagamentos por turma e aluno.</p></div>
  <div class="actions"><a class="btn primary" href="novo_pagamento.php">Novo pagamento</a></div>
</section>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Filtrar aluno</h2></div>
  <div class="panel-body">
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
    <form class="form-grid" method="get">
      <label class="field span-2">
        <span>Turma</span>
        <select class="select" id="pag-turma" name="id_turma" data-turma-filter data-filter-students="#pag-aluno">
          <option value="">Selecione a turma</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($turmas as $turma): ?><option value="<?= e($turma['id_turma']) ?>" <?= $idTurmaFiltro === (int) $turma['id_turma'] ? 'selected' : '' ?>><?= e($turma['codigo_turma']) ?> - <?= e($turma['curso']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="field">
        <span>Buscar aluno</span>
        <input class="control" type="text" placeholder="Nome ou matricula" data-student-search="#pag-aluno" data-turma-source="#pag-turma">
      </label>
      <label class="field">
        <span>Aluno</span>
        <select class="select" id="pag-aluno" name="id_aluno">
          <option value="">Selecione a turma primeiro</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($alunos as $aluno): ?>
            <option value="<?= e($aluno['id_aluno']) ?>" data-turma="<?= e($aluno['id_turma']) ?>" data-search="<?= e($aluno['nome'] . ' ' . $aluno['matricula']) ?>" hidden disabled <?= $idAlunoFiltro === (int) $aluno['id_aluno'] ? 'selected' : '' ?>><?= e($aluno['nome']) ?> - <?= e($aluno['matricula']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <div class="actions">
        <button class="btn primary" type="submit">Buscar pagamentos</button>
        <a class="btn ghost" href="pagamentos.php">Limpar filtro</a>
      </div>
    </form>
  </div>
</section>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Pagamentos do aluno</h2></div>
  <div class="panel-body">
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
    <?php if (!$alunoSelecionado): ?>
      <div class="alert warning">
        <span class="alert-marker"></span>
        <div><strong>Selecione um aluno</strong><span class="muted">Escolha uma turma e um aluno para visualizar os pagamentos.</span></div>
      </div>
    <?php endif; ?>
  </div>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
  <?php if ($alunoSelecionado): ?>
    <div class="table-wrap">
<!-- RETORNO - Tabela apresenta ao usuario os dados consultados no banco. -->
      <table id="tabela-principal">
        <thead><tr><th>Aluno</th><th>Valor</th><th>Vencimento</th><th>Status</th><th>Acoes</th></tr></thead>
        <tbody>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
          <?php if (empty($pagamentos)): ?><tr><td colspan="5">Nenhum pagamento encontrado para este aluno.</td></tr><?php endif; ?>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($pagamentos as $pagamento): ?>
            <tr>
              <td><?= e($alunoSelecionado['nome']) ?></td><td><?= e(dinheiro($pagamento['valor'])) ?></td><td><?= e(dataBr($pagamento['vencimento'])) ?></td><td><?= badge($pagamento['status']) ?></td>
              <td class="table-actions"><a class="btn ghost" href="novo_pagamento.php?editar=<?= e($pagamento['id_pagamento']) ?>">Editar</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->