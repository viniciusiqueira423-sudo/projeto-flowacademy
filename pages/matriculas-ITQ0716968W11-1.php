<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Lista matriculas ativas e seus relacionamentos entre alunos e turmas.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin', 'coordenacao', 'administrativo'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Nova Matricula';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'matriculas';
// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('acao') === 'salvar') {
    // TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
    try {
        // PROCESSAMENTO - Variavel $idTurma guarda estado ou dados usados nas proximas etapas.
        $idTurma = (int) post('id_turma');
        // PROCESSAMENTO - Variavel $idAluno guarda estado ou dados usados nas proximas etapas.
        $idAluno = (int) post('id_aluno');

        // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
        $turma = buscarUm(
            'SELECT t.capacidade_maxima, COUNT(m.id_matricula) AS total
             FROM turmas t
             LEFT JOIN matriculas m ON m.id_turma = t.id_turma AND m.status = "ativa"
             WHERE t.id_turma = :id_turma
             GROUP BY t.id_turma',
            [':id_turma' => $idTurma]
        );

        // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
        if ($turma && (int) $turma['total'] >= (int) $turma['capacidade_maxima']) {
            // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
            $erro = 'A turma selecionada ja esta lotada.';
        } else {
            // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
            executar(
                'INSERT INTO matriculas (id_aluno, id_turma, data_matricula, status)
                 VALUES (:id_aluno, :id_turma, :data_matricula, :status)',
                [
                    ':id_aluno' => $idAluno,
                    ':id_turma' => $idTurma,
                    ':data_matricula' => date('Y-m-d'),
                    ':status' => 'ativa',
                ]
            );
            // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
            registrarLog('Cadastrou matricula');
            // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
            flash('success', 'Matricula realizada com sucesso.');
            // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
            redirecionar('matriculas.php');
        }
    } catch (Throwable $erroSql) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = $erroSql->getMessage();
    }
}

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$alunos = buscarTodos(
    'SELECT a.id_aluno, a.matricula, u.nome
     FROM alunos a
     JOIN usuarios u ON u.id_usuario = a.id_usuario
     ORDER BY u.nome'
);
// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$turmas = buscarTodos(
    'SELECT t.id_turma, t.codigo_turma, c.nome AS curso
     FROM turmas t
     JOIN cursos c ON c.id_curso = t.id_curso
     WHERE t.status = "ativa"
     ORDER BY t.codigo_turma'
);

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading"><div><span class="eyebrow">Matricula</span><h1>Nova Matricula</h1><p>Matricula o aluno em uma turma ativa, respeitando duplicidade e capacidade.</p></div></section>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
<?php if ($erro): ?><div class="alert danger"><span class="alert-marker"></span><div><strong>Erro</strong><span class="muted"><?= e($erro) ?></span></div></div><?php endif; ?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Dados da matricula</h2></div>
  <div class="panel-body">
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
    <form class="form-grid" method="post">
      <input type="hidden" name="acao" value="salvar">
      <label class="field"><span>Aluno</span>
        <select class="select" name="id_aluno" required>
          <option value="">Selecione</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($alunos as $aluno): ?><option value="<?= e($aluno['id_aluno']) ?>"><?= e($aluno['nome']) ?> - <?= e($aluno['matricula']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Turma</span>
        <select class="select" name="id_turma" required>
          <option value="">Selecione</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($turmas as $turma): ?><option value="<?= e($turma['id_turma']) ?>"><?= e($turma['codigo_turma']) ?> - <?= e($turma['curso']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <div class="actions span-2"><button class="btn primary" type="submit">Realizar matricula</button></div>
    </form>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->