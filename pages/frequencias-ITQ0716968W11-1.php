<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Registra e consulta frequencias, respeitando regras de perfil do usuario.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin', 'coordenacao', 'professor', 'aluno'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'frequencias';
// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';
// PROCESSAMENTO - Variavel $usuario guarda estado ou dados usados nas proximas etapas.
$usuario = usuarioLogado();
// PROCESSAMENTO - Variavel $souAluno guarda estado ou dados usados nas proximas etapas.
$souAluno = $usuario['perfil'] === 'aluno';
// PROCESSAMENTO - Variavel $souProfessor guarda estado ou dados usados nas proximas etapas.
$souProfessor = $usuario['perfil'] === 'professor'; // NOVO
// PROCESSAMENTO - Variavel $podeEditar guarda estado ou dados usados nas proximas etapas.
$podeEditar = !$souAluno;
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = $souAluno ? 'Frequencia' : 'Registrar Frequencia';

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $podeEditar) {
    // TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
    try {
        // NOVO
        // Impede que o professor lance dados em turmas que nao sao dele.
        // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
        if ($souProfessor) { // NOVO
            // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
            $permissao = buscarUm(
                'SELECT 1
                 FROM matriculas m
                 JOIN turmas t ON t.id_turma = m.id_turma
                 JOIN professores p ON p.id_professor = t.id_professor
                 WHERE p.id_usuario = :id_usuario
                   AND m.id_matricula = :id_matricula',
                [
                    ':id_usuario' => $usuario['id_usuario'],
                    ':id_matricula' => (int) post('id_matricula'),
                ]
            );

            // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
            if (!$permissao) {
                throw new Exception('Você não possui permissão para lançar dados nesta turma.'); // NOVO
            }
        }

        // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
        executar(
            'INSERT INTO frequencia (id_matricula, id_disciplina, total_aulas, presencas)
             VALUES (:id_matricula, :id_disciplina, :total_aulas, :presencas)
             ON DUPLICATE KEY UPDATE
                total_aulas = VALUES(total_aulas),
                presencas = VALUES(presencas)',
            [
                ':id_matricula' => (int) post('id_matricula'),
                ':id_disciplina' => (int) post('id_disciplina'),
                ':total_aulas' => (int) post('total_aulas'),
                ':presencas' => (int) post('presencas'),
            ]
        );
        // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
        registrarLog('Registrou frequencia');
        // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
        flash('success', 'Frequencia salva com sucesso.');
        // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
        redirecionar('frequencias.php');
    } catch (Throwable $erroSql) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = $erroSql->getMessage();
    }
}

// PROCESSAMENTO - Variavel $temMatricula guarda estado ou dados usados nas proximas etapas.
$temMatricula = true;
// PROCESSAMENTO - Variavel $parametros guarda estado ou dados usados nas proximas etapas.
$parametros = [];
// PROCESSAMENTO - Variavel $whereAluno guarda estado ou dados usados nas proximas etapas.
$whereAluno = '';
// PROCESSAMENTO - Variavel $joinProfessor guarda estado ou dados usados nas proximas etapas.
$joinProfessor = ''; // NOVO

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if ($souAluno) {
    // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
    $aluno = buscarUm('SELECT id_aluno FROM alunos WHERE id_usuario = :id', [':id' => $usuario['id_usuario']]);
    // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
    $temMatricula = (bool) buscarUm('SELECT id_matricula FROM matriculas WHERE id_aluno = :id AND status = "ativa"', [':id' => $aluno['id_aluno'] ?? 0]);
    // PROCESSAMENTO - Variavel $whereAluno guarda estado ou dados usados nas proximas etapas.
    $whereAluno = 'WHERE a.id_usuario = :id_usuario';
    $parametros[':id_usuario'] = $usuario['id_usuario'];
}

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if ($souProfessor) { // NOVO
    // PROCESSAMENTO - Variavel $joinProfessor guarda estado ou dados usados nas proximas etapas.
    $joinProfessor = 'JOIN professores p ON p.id_professor = t.id_professor'; // NOVO
    // PROCESSAMENTO - Variavel $whereAluno guarda estado ou dados usados nas proximas etapas.
    $whereAluno = 'WHERE p.id_usuario = :id_usuario'; // NOVO
    $parametros[':id_usuario'] = $usuario['id_usuario']; // NOVO
}

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if ($podeEditar) {
    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if ($souProfessor) { // NOVO
        // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
        $turmas = buscarTodos(
            'SELECT
                t.id_turma,
                t.id_curso,
                t.codigo_turma,
                c.nome AS curso
             FROM turmas t
             JOIN cursos c ON c.id_curso = t.id_curso
             JOIN professores p ON p.id_professor = t.id_professor
             WHERE p.id_usuario = :id_usuario
               AND t.status = "ativa"
             ORDER BY t.codigo_turma',
            [
                ':id_usuario' => $usuario['id_usuario'],
            ]
        );

        // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
        $matriculas = buscarTodos(
            'SELECT m.id_matricula, m.id_turma, u.nome AS aluno, a.matricula
             FROM matriculas m
             JOIN turmas t ON t.id_turma = m.id_turma
             JOIN professores p ON p.id_professor = t.id_professor
             JOIN alunos a ON a.id_aluno = m.id_aluno
             JOIN usuarios u ON u.id_usuario = a.id_usuario
             WHERE p.id_usuario = :id_usuario
               AND m.status = "ativa"
             ORDER BY u.nome',
            [
                ':id_usuario' => $usuario['id_usuario'],
            ]
        );
    } else { // ALTERADO
        // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
        $turmas = buscarTodos(
            'SELECT t.id_turma, t.id_curso, t.codigo_turma, c.nome AS curso
             FROM turmas t JOIN cursos c ON c.id_curso = t.id_curso
             WHERE t.status = "ativa"
             ORDER BY t.codigo_turma'
        );
        // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
        $matriculas = buscarTodos(
            'SELECT m.id_matricula, m.id_turma, u.nome AS aluno, a.matricula
             FROM matriculas m
             JOIN alunos a ON a.id_aluno = m.id_aluno
             JOIN usuarios u ON u.id_usuario = a.id_usuario
             WHERE m.status = "ativa"
             ORDER BY u.nome'
        );
    }
    // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
    $disciplinasPorCurso = buscarTodos('SELECT id_disciplina, id_curso, nome FROM disciplinas ORDER BY nome');
    // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
    $disciplinasFiltro = buscarTodos('SELECT id_disciplina, nome FROM disciplinas ORDER BY nome');
}

// PROCESSAMENTO - Variavel $filtroUc guarda estado ou dados usados nas proximas etapas.
$filtroUc = getValor('uc');
// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if ($filtroUc !== '') {
    // PROCESSAMENTO - Variavel $whereAluno guarda estado ou dados usados nas proximas etapas.
    $whereAluno = ($whereAluno === '' ? 'WHERE' : $whereAluno . ' AND') . ' f.id_disciplina = :id_disciplina';
    $parametros[':id_disciplina'] = (int) $filtroUc;
}

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$frequencias = buscarTodos(
    'SELECT f.*, u.nome AS aluno, a.matricula, t.codigo_turma, d.nome AS disciplina
     FROM frequencia f
     JOIN matriculas m ON m.id_matricula = f.id_matricula
     JOIN alunos a ON a.id_aluno = m.id_aluno
     JOIN usuarios u ON u.id_usuario = a.id_usuario
     JOIN turmas t ON t.id_turma = m.id_turma
     ' . $joinProfessor . '
     JOIN disciplinas d ON d.id_disciplina = f.id_disciplina
     ' . $whereAluno . '
     ORDER BY u.nome, d.nome',
    $parametros
);

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
<?php if ($souAluno): ?>
<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
  <section class="page-heading"><div><span class="eyebrow">Presenca</span><h1>Frequencia</h1><p>Acompanhamento por unidade curricular.</p></div></section>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
  <section class="panel">
    <div class="panel-header"><h2>Resumo de frequencia</h2></div>
    <div class="panel-body">
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
      <?php if (!$temMatricula): ?>
        <div class="alert warning">
          <span class="alert-marker"></span>
          <div><strong>Sem matricula</strong><span class="muted">Nenhuma matricula foi encontrada para este aluno.</span></div>
        </div>
      <?php endif; ?>
    </div>
    <div class="table-wrap">
<!-- RETORNO - Tabela apresenta ao usuario os dados consultados no banco. -->
      <table id="tabela-principal">
        <thead><tr><th>UC</th><th>Total de aulas</th><th>Presencas</th><th>Percentual</th><th>Situacao</th></tr></thead>
        <tbody>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
          <?php if (empty($frequencias)): ?><tr><td colspan="5">Nenhum registro de frequencia encontrado.</td></tr><?php endif; ?>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($frequencias as $frequencia): ?>
            <tr>
              <td><?= e($frequencia['disciplina']) ?></td><td><?= e($frequencia['total_aulas']) ?></td><td><?= e($frequencia['presencas']) ?></td>
              <td><?= e(number_format((float) $frequencia['percentual'], 0)) ?>%</td>
              <td><?= badge((float) $frequencia['percentual'] >= 75 ? 'regular' : 'atrasado') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

<?php else: ?>
<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
  <section class="page-heading"><div><span class="eyebrow">Chamada</span><h1>Registrar Frequencia</h1><p>Atualize total de aulas e presencas com validacao simples.</p></div></section>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
  <?php if ($erro): ?><div class="alert danger"><span class="alert-marker"></span><div><strong>Erro</strong><span class="muted"><?= e($erro) ?></span></div></div><?php endif; ?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
  <section class="panel">
    <div class="panel-header"><h2>Novo registro</h2></div>
    <div class="panel-body">
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
      <form class="form-grid" method="post">
        <label class="field span-2">
          <span>Turma</span>
          <select class="select" id="freq-turma" data-turma-filter data-filter-students="#freq-aluno" data-filter-ucs="#freq-uc" required>
            <option value="">Selecione a turma</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
            <?php foreach ($turmas as $turma): ?>
              <option value="<?= e($turma['id_turma']) ?>"><?= e($turma['codigo_turma']) ?> - <?= e($turma['curso']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="field span-2">
          <span>Aluno matriculado</span>
          <select class="select" id="freq-aluno" name="id_matricula" required>
            <option value="">Selecione a turma primeiro</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
            <?php foreach ($matriculas as $matricula): ?>
              <option value="<?= e($matricula['id_matricula']) ?>" data-turma="<?= e($matricula['id_turma']) ?>" hidden disabled><?= e($matricula['aluno']) ?> - <?= e($matricula['matricula']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="field span-2">
          <span>Unidade curricular</span>
          <select class="select" id="freq-uc" name="id_disciplina" required>
            <option value="">Selecione a turma primeiro</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
            <?php foreach ($turmas as $turma): ?>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
              <?php foreach ($disciplinasPorCurso as $disciplina): ?>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
                <?php if ((int) $disciplina['id_curso'] === (int) $turma['id_curso']): ?>
                  <option value="<?= e($disciplina['id_disciplina']) ?>" data-turma="<?= e($turma['id_turma']) ?>" hidden disabled><?= e($disciplina['nome']) ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="field"><span>Total de aulas</span><input class="control" type="number" name="total_aulas" min="1" required></label>
        <label class="field"><span>Presencas</span><input class="control" type="number" name="presencas" min="0" required></label>

        <div class="actions span-2"><button class="btn primary" type="submit">Salvar frequencia</button></div>
      </form>
    </div>
  </section>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
  <section class="panel">
    <div class="panel-header"><h2>Frequencias por UC</h2></div>
    <div class="panel-body">
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
      <form class="form-grid" method="get">
        <label class="field span-2">
          <span>Filtrar por UC</span>
          <select class="select" name="uc" onchange="this.form.submit()">
            <option value="">Todas as unidades curriculares</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
            <?php foreach ($disciplinasFiltro as $disciplina): ?>
              <option value="<?= e($disciplina['id_disciplina']) ?>" <?= $filtroUc === (string) $disciplina['id_disciplina'] ? 'selected' : '' ?>><?= e($disciplina['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </form>
    </div>
    <div class="table-wrap">
<!-- RETORNO - Tabela apresenta ao usuario os dados consultados no banco. -->
      <table id="tabela-principal">
        <thead><tr><th>Aluno</th><th>Turma</th><th>Aulas</th><th>Presencas</th><th>Percentual</th><th>Situacao</th></tr></thead>
        <tbody>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
          <?php if (empty($frequencias)): ?><tr><td colspan="6">Nenhum registro de frequencia encontrado.</td></tr><?php endif; ?>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($frequencias as $frequencia): ?>
            <tr>
              <td><strong><?= e($frequencia['aluno']) ?></strong><br><span class="muted"><?= e($frequencia['matricula']) ?></span></td>
              <td><?= e($frequencia['codigo_turma']) ?></td>
              <td><?= e($frequencia['total_aulas']) ?></td><td><?= e($frequencia['presencas']) ?></td>
              <td><?= e(number_format((float) $frequencia['percentual'], 0)) ?>%</td>
              <td><?= badge((float) $frequencia['percentual'] >= 75 ? 'regular' : 'atrasado') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->