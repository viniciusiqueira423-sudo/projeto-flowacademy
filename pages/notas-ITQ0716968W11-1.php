<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Lanca notas, calcula medias e lista registros conforme perfil/permissao.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin', 'coordenacao', 'professor'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Lancar Notas';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'notas';
// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';
// PROCESSAMENTO - Variavel $usuario guarda estado ou dados usados nas proximas etapas.
$usuario = usuarioLogado(); // NOVO
// PROCESSAMENTO - Variavel $souProfessor guarda estado ou dados usados nas proximas etapas.
$souProfessor = $usuario['perfil'] === 'professor'; // NOVO

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
    try {

        // ===================== NOVO =====================
        // Impede que o professor lance notas em turmas que não são dele.
        // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
        if ($souProfessor) { // ALTERADO

            // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
            $permissao = buscarUm(
                'SELECT 1
                 FROM matriculas m
                 JOIN turmas t
                    ON t.id_turma = m.id_turma
                 JOIN professores p
                    ON p.id_professor = t.id_professor
                 WHERE p.id_usuario = :id_usuario
                   AND m.id_matricula = :id_matricula',
                [
                    ':id_usuario'   => $usuario['id_usuario'], // ALTERADO
                    ':id_matricula' => (int) post('id_matricula')
                ]
            );

            // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
            if (!$permissao) {
                throw new Exception('Você não possui permissão para lançar dados nesta turma.'); // ALTERADO
            }
        }
        // =================== FIM NOVO ===================

        // PROCESSAMENTO - Variavel $p1 guarda estado ou dados usados nas proximas etapas.
        $p1 = (float) str_replace(',', '.', post('prova_1'));
        // PROCESSAMENTO - Variavel $p2 guarda estado ou dados usados nas proximas etapas.
        $p2 = (float) str_replace(',', '.', post('prova_2'));
        // PROCESSAMENTO - Variavel $trabalho guarda estado ou dados usados nas proximas etapas.
        $trabalho = (float) str_replace(',', '.', post('trabalho'));
        // PROCESSAMENTO - Variavel $comportamental guarda estado ou dados usados nas proximas etapas.
        $comportamental = (float) str_replace(',', '.', post('comportamental'));

        // PROCESSAMENTO - Variavel $media guarda estado ou dados usados nas proximas etapas.
        $media = round(
            ($p1 * 0.3) +
            ($p2 * 0.3) +
            ($trabalho * 0.3) +
            ($comportamental * 0.1),
            2
        );

        // PROCESSAMENTO - Variavel $status guarda estado ou dados usados nas proximas etapas.
        $status = $media >= 6 ? 'aprovado' : 'reprovado';

        // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
        executar(
            'INSERT INTO notas
            (
                id_matricula,
                id_disciplina,
                prova_1,
                prova_2,
                trabalho,
                comportamental,
                media_uc,
                status
            )
            VALUES
            (
                :id_matricula,
                :id_disciplina,
                :prova_1,
                :prova_2,
                :trabalho,
                :comportamental,
                :media_uc,
                :status
            )
            ON DUPLICATE KEY UPDATE

                prova_1 = VALUES(prova_1),
                prova_2 = VALUES(prova_2),
                trabalho = VALUES(trabalho),
                comportamental = VALUES(comportamental),
                media_uc = VALUES(media_uc),
                status = VALUES(status),
                data_lancamento = NOW()',
            [
                ':id_matricula'    => (int) post('id_matricula'),
                ':id_disciplina'   => (int) post('id_disciplina'),
                ':prova_1'         => $p1,
                ':prova_2'         => $p2,
                ':trabalho'        => $trabalho,
                ':comportamental'  => $comportamental,
                ':media_uc'        => $media,
                ':status'          => $status,
            ]
        );

        // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
        registrarLog('Lancou nota');

        // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
        flash('success', 'Nota salva com sucesso.');

        // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
        redirecionar('notas.php');

    } catch (Throwable $erroSql) {

        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = $erroSql->getMessage();

    }

}


// ======================================================================
// ALTERADO
// Professor vê somente suas turmas.
// Admin e Coordenação veem todas.
// ======================================================================

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if ($souProfessor) { // ALTERADO

    // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
    $turmas = buscarTodos(
        'SELECT
            t.id_turma,
            t.id_curso,
            t.codigo_turma,
            c.nome AS curso
         FROM turmas t
         JOIN cursos c
            ON c.id_curso = t.id_curso
         JOIN professores p
            ON p.id_professor = t.id_professor
         WHERE p.id_usuario = :id_usuario
           AND t.status = "ativa"
         ORDER BY t.codigo_turma',
        [
            ':id_usuario' => $usuario['id_usuario'] // ALTERADO
        ]
    );

    // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
    $matriculas = buscarTodos(
        'SELECT
            m.id_matricula,
            m.id_turma,
            u.nome AS aluno,
            a.matricula
         FROM matriculas m
         JOIN turmas t
            ON t.id_turma = m.id_turma
         JOIN professores p
            ON p.id_professor = t.id_professor
         JOIN alunos a
            ON a.id_aluno = m.id_aluno
         JOIN usuarios u
            ON u.id_usuario = a.id_usuario
         WHERE p.id_usuario = :id_usuario
           AND m.status = "ativa"
         ORDER BY u.nome',
        [
            ':id_usuario' => $usuario['id_usuario'] // ALTERADO
        ]
    );

} else {

    // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
    $turmas = buscarTodos(
        'SELECT
            t.id_turma,
            t.id_curso,
            t.codigo_turma,
            c.nome AS curso
         FROM turmas t
         JOIN cursos c
            ON c.id_curso = t.id_curso
         WHERE t.status = "ativa"
         ORDER BY t.codigo_turma'
    );

    // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
    $matriculas = buscarTodos(
        'SELECT
            m.id_matricula,
            m.id_turma,
            u.nome AS aluno,
            a.matricula
         FROM matriculas m
         JOIN alunos a
            ON a.id_aluno = m.id_aluno
         JOIN usuarios u
            ON u.id_usuario = a.id_usuario
         WHERE m.status = "ativa"
         ORDER BY u.nome'
    );

}

// ======================================================================
// FIM ALTERAÇÃO
// ======================================================================

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$disciplinasPorCurso = buscarTodos(
    'SELECT
        id_disciplina,
        id_curso,
        nome
     FROM disciplinas
     ORDER BY nome'
);

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$disciplinasFiltro = buscarTodos(
    'SELECT
        id_disciplina,
        nome
     FROM disciplinas
     ORDER BY nome'
);

// PROCESSAMENTO - Variavel $filtroUc guarda estado ou dados usados nas proximas etapas.
$filtroUc = getValor('uc');

// PROCESSAMENTO - Variavel $parametrosNotas guarda estado ou dados usados nas proximas etapas.
$parametrosNotas = [];
// PROCESSAMENTO - Variavel $whereUc guarda estado ou dados usados nas proximas etapas.
$whereUc = '';

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if ($filtroUc !== '') {

    // PROCESSAMENTO - Variavel $whereUc guarda estado ou dados usados nas proximas etapas.
    $whereUc = 'WHERE n.id_disciplina = :id_disciplina';

    $parametrosNotas[':id_disciplina'] = (int) $filtroUc;
}

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if ($souProfessor) { // ALTERADO

    // PROCESSAMENTO - Variavel $whereProfessor guarda estado ou dados usados nas proximas etapas.
    $whereProfessor = ($whereUc == '')
        ? 'WHERE p.id_usuario = :id_usuario'
        : $whereUc . ' AND p.id_usuario = :id_usuario';

    $parametrosNotas[':id_usuario'] = $usuario['id_usuario']; // ALTERADO

    // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
    $notas = buscarTodos(
        'SELECT
            n.*,
            u.nome AS aluno,
            a.matricula,
            t.codigo_turma,
            d.nome AS disciplina
         FROM notas n
         JOIN matriculas m
            ON m.id_matricula = n.id_matricula
         JOIN turmas t
            ON t.id_turma = m.id_turma
         JOIN professores p
            ON p.id_professor = t.id_professor
         JOIN alunos a
            ON a.id_aluno = m.id_aluno
         JOIN usuarios u
            ON u.id_usuario = a.id_usuario
         JOIN disciplinas d
            ON d.id_disciplina = n.id_disciplina
         ' . $whereProfessor . '
         ORDER BY n.data_lancamento DESC',
        $parametrosNotas
    );

} else {

    // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
    $notas = buscarTodos(
        'SELECT
            n.*,
            u.nome AS aluno,
            a.matricula,
            t.codigo_turma,
            d.nome AS disciplina
         FROM notas n
         JOIN matriculas m
            ON m.id_matricula = n.id_matricula
         JOIN alunos a
            ON a.id_aluno = m.id_aluno
         JOIN usuarios u
            ON u.id_usuario = a.id_usuario
         JOIN turmas t
            ON t.id_turma = m.id_turma
         JOIN disciplinas d
            ON d.id_disciplina = n.id_disciplina
         ' . $whereUc . '
         ORDER BY n.data_lancamento DESC',
        $parametrosNotas
    );

}
// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading"><div><span class="eyebrow">Avaliacao</span><h1>Lancar Notas</h1><p>A media e calculada no sistema: provas 60%, trabalho 30% e comportamental 10%.</p></div></section>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
<?php if ($erro): ?><div class="alert danger"><span class="alert-marker"></span><div><strong>Erro</strong><span class="muted"><?= e($erro) ?></span></div></div><?php endif; ?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Novo lancamento</h2></div>
  <div class="panel-body">
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
    <form class="form-grid" method="post">
      <label class="field span-2">
        <span>Turma</span>
        <select class="select" id="notas-turma" data-turma-filter data-filter-students="#notas-aluno" data-filter-ucs="#notas-uc" required>
          <option value="">Selecione a turma</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($turmas as $turma): ?>
            <option value="<?= e($turma['id_turma']) ?>"><?= e($turma['codigo_turma']) ?> - <?= e($turma['curso']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="field span-2">
        <span>Aluno matriculado</span>
        <select class="select" id="notas-aluno" name="id_matricula" required>
          <option value="">Selecione a turma primeiro</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($matriculas as $matricula): ?>
            <option value="<?= e($matricula['id_matricula']) ?>" data-turma="<?= e($matricula['id_turma']) ?>" hidden disabled><?= e($matricula['aluno']) ?> - <?= e($matricula['matricula']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="field span-2">
        <span>Unidade curricular</span>
        <select class="select" id="notas-uc" name="id_disciplina" required>
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

      <label class="field"><span>Prova 1</span><input class="control" name="prova_1" type="number" step="0.01" min="0" max="10" required></label>
      <label class="field"><span>Prova 2</span><input class="control" name="prova_2" type="number" step="0.01" min="0" max="10" required></label>
      <label class="field"><span>Trabalho</span><input class="control" name="trabalho" type="number" step="0.01" min="0" max="10" required></label>
      <label class="field"><span>Comportamental</span><input class="control" name="comportamental" type="number" step="0.01" min="0" max="10" required></label>

      <div class="actions span-2"><button class="btn primary" type="submit">Salvar notas</button></div>
    </form>
  </div>
</section>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Notas lancadas</h2></div>
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
      <thead><tr><th>Aluno</th><th>Turma</th><th>P1</th><th>P2</th><th>Trabalho</th><th>Comp.</th><th>Media</th><th>Status</th></tr></thead>
      <tbody>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
        <?php if (empty($notas)): ?><tr><td colspan="8">Nenhuma nota encontrada.</td></tr><?php endif; ?>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
        <?php foreach ($notas as $nota): ?>
          <tr>
            <td><strong><?= e($nota['aluno']) ?></strong><br><span class="muted"><?= e($nota['matricula']) ?></span></td>
            <td><?= e($nota['codigo_turma']) ?></td>
            <td><?= e($nota['prova_1']) ?></td><td><?= e($nota['prova_2']) ?></td><td><?= e($nota['trabalho']) ?></td><td><?= e($nota['comportamental']) ?></td><td><?= e($nota['media_uc']) ?></td><td><?= badge($nota['status']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->