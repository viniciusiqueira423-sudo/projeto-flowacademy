<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Cria ou edita turmas, vinculando curso, professor, turno, periodo e capacidade.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin', 'coordenacao'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Nova Turma';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'nova_turma';
// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';
// PROCESSAMENTO - Variavel $editando guarda estado ou dados usados nas proximas etapas.
$editando = null;

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('acao') === 'salvar') {
    // TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
    try {
        // PROCESSAMENTO - Variavel $idTurma guarda estado ou dados usados nas proximas etapas.
        $idTurma = (int) post('id_turma');
        // PROCESSAMENTO - Variavel $sqlDados guarda estado ou dados usados nas proximas etapas.
        $sqlDados = [
            ':id_curso' => (int) post('id_curso'),
            ':id_professor' => (int) post('id_professor'),
            ':codigo_turma' => post('codigo_turma'),
            ':turno' => post('turno'),
            ':periodo_letivo' => post('periodo_letivo'),
            ':capacidade_maxima' => (int) post('capacidade_maxima'),
            ':status' => post('status'),
        ];

        // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
        if ($idTurma > 0) {
            // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
            executar(
                'UPDATE turmas
                 SET id_curso = :id_curso, id_professor = :id_professor, codigo_turma = :codigo_turma,
                     turno = :turno, periodo_letivo = :periodo_letivo, capacidade_maxima = :capacidade_maxima,
                     status = :status
                 WHERE id_turma = :id_turma',
                $sqlDados + [':id_turma' => $idTurma]
            );
            // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
            flash('success', 'Turma atualizada com sucesso.');
        } else {
            // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
            executar(
                'INSERT INTO turmas (id_curso, id_professor, codigo_turma, turno, periodo_letivo, capacidade_maxima, status)
                 VALUES (:id_curso, :id_professor, :codigo_turma, :turno, :periodo_letivo, :capacidade_maxima, :status)',
                $sqlDados
            );
            // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
            flash('success', 'Turma cadastrada com sucesso.');
        }
        // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
        registrarLog('Salvou turma');
        // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
        redirecionar('turmas.php');
    } catch (Throwable $erroSql) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = $erroSql->getMessage();
    }
}

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if (getValor('editar') !== '') {
    // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
    $editando = buscarUm('SELECT * FROM turmas WHERE id_turma = :id', [':id' => (int) getValor('editar')]);
}

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$cursos = buscarTodos('SELECT id_curso, nome FROM cursos ORDER BY nome');
// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$professores = buscarTodos(
    'SELECT p.id_professor, u.nome
     FROM professores p
     JOIN usuarios u ON u.id_usuario = p.id_usuario
     ORDER BY u.nome'
);

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading"><div><span class="eyebrow">Cadastro</span><h1><?= $editando ? 'Editar Turma' : 'Nova Turma' ?></h1><p>Crie uma turma vinculando curso e professor.</p></div></section>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
<?php if ($erro): ?><div class="alert danger"><span class="alert-marker"></span><div><strong>Erro</strong><span class="muted"><?= e($erro) ?></span></div></div><?php endif; ?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Dados da turma</h2></div>
  <div class="panel-body">
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
    <form class="form-grid" method="post">
      <input type="hidden" name="acao" value="salvar"><input type="hidden" name="id_turma" value="<?= e($editando['id_turma'] ?? '') ?>">

      <label class="field"><span>Curso</span>
        <select class="select" name="id_curso" required>
          <option value="">Selecione</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($cursos as $curso): ?><option value="<?= e($curso['id_curso']) ?>" <?= (int) ($editando['id_curso'] ?? 0) === (int) $curso['id_curso'] ? 'selected' : '' ?>><?= e($curso['nome']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Professor</span>
        <select class="select" name="id_professor" required>
          <option value="">Selecione</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($professores as $professor): ?><option value="<?= e($professor['id_professor']) ?>" <?= (int) ($editando['id_professor'] ?? 0) === (int) $professor['id_professor'] ? 'selected' : '' ?>><?= e($professor['nome']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Codigo da turma</span><input class="control" name="codigo_turma" placeholder="TI-1A" value="<?= e($editando['codigo_turma'] ?? '') ?>" required></label>
      <label class="field"><span>Turno</span>
        <select class="select" name="turno">
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach (['noite', 'manha', 'tarde'] as $turno): ?><option value="<?= e($turno) ?>" <?= ($editando['turno'] ?? 'noite') === $turno ? 'selected' : '' ?>><?= e(textoStatus($turno)) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Periodo letivo</span><input class="control" name="periodo_letivo" placeholder="2026.1" value="<?= e($editando['periodo_letivo'] ?? '') ?>" required></label>
      <label class="field"><span>Capacidade</span><input class="control" type="number" name="capacidade_maxima" value="<?= e($editando['capacidade_maxima'] ?? 35) ?>"></label>
      <label class="field"><span>Status</span>
        <select class="select" name="status">
          <option value="ativa" <?= ($editando['status'] ?? 'ativa') === 'ativa' ? 'selected' : '' ?>>Ativa</option>
          <option value="encerrada" <?= ($editando['status'] ?? '') === 'encerrada' ? 'selected' : '' ?>>Encerrada</option>
        </select>
      </label>

      <div class="actions"><button class="btn primary" type="submit">Salvar turma</button><a class="btn ghost" href="turmas.php">Voltar</a></div>
    </form>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->