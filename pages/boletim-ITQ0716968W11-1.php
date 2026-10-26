<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Exibe notas por unidade curricular, com visao limitada para alunos.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['aluno', 'admin', 'coordenacao'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Boletim';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'boletim';
// PROCESSAMENTO - Variavel $usuario guarda estado ou dados usados nas proximas etapas.
$usuario = usuarioLogado();
// PROCESSAMENTO - Variavel $souAluno guarda estado ou dados usados nas proximas etapas.
$souAluno = $usuario['perfil'] === 'aluno';

// PROCESSAMENTO - Variavel $parametros guarda estado ou dados usados nas proximas etapas.
$parametros = [];
// PROCESSAMENTO - Variavel $whereAluno guarda estado ou dados usados nas proximas etapas.
$whereAluno = '';
// PROCESSAMENTO - Variavel $temMatricula guarda estado ou dados usados nas proximas etapas.
$temMatricula = true;

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if ($souAluno) {
    // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
    $aluno = buscarUm('SELECT * FROM alunos WHERE id_usuario = :id', [':id' => $usuario['id_usuario']]);
    // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
    $temMatricula = (bool) buscarUm('SELECT id_matricula FROM matriculas WHERE id_aluno = :id AND status = "ativa"', [':id' => $aluno['id_aluno'] ?? 0]);
    // PROCESSAMENTO - Variavel $whereAluno guarda estado ou dados usados nas proximas etapas.
    $whereAluno = 'WHERE a.id_usuario = :id_usuario';
    $parametros[':id_usuario'] = $usuario['id_usuario'];
}

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$notas = buscarTodos(
    'SELECT u.nome AS aluno, a.matricula, t.codigo_turma, c.nome AS curso,
            d.nome AS disciplina, n.prova_1, n.prova_2, n.trabalho,
            n.comportamental, n.media_uc, n.status
     FROM notas n
     JOIN matriculas m ON m.id_matricula = n.id_matricula
     JOIN alunos a ON a.id_aluno = m.id_aluno
     JOIN usuarios u ON u.id_usuario = a.id_usuario
     JOIN turmas t ON t.id_turma = m.id_turma
     JOIN cursos c ON c.id_curso = t.id_curso
     JOIN disciplinas d ON d.id_disciplina = n.id_disciplina
     ' . $whereAluno . '
     ORDER BY u.nome, d.nome',
    $parametros
);

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading"><div><span class="eyebrow">Notas</span><h1>Boletim</h1><p>Notas por unidade curricular, usando a media ponderada do SQL.</p></div></section>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Unidades curriculares</h2></div>
  <div class="panel-body">
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
    <?php if ($souAluno && !$temMatricula): ?>
      <div class="alert warning">
        <span class="alert-marker"></span>
        <div><strong>Sem matricula</strong><span class="muted">Nenhuma matricula foi encontrada para este aluno.</span></div>
      </div>
    <?php endif; ?>
  </div>
  <div class="table-wrap">
<!-- RETORNO - Tabela apresenta ao usuario os dados consultados no banco. -->
    <table id="tabela-principal">
      <thead>
        <tr>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
          <?php if (!$souAluno): ?><th>Aluno</th><th>Turma</th><?php endif; ?>
          <th>UC</th><th>Prova 1</th><th>Prova 2</th><th>Trabalho</th><th>Comportamental</th><th>Media</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
        <?php if (empty($notas)): ?>
          <tr><td colspan="<?= $souAluno ? 6 : 8 ?>">Nenhuma nota encontrada.</td></tr>
        <?php endif; ?>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
        <?php foreach ($notas as $nota): ?>
          <tr>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
            <?php if (!$souAluno): ?><td><?= e($nota['aluno']) ?></td><td><?= e($nota['codigo_turma']) ?></td><?php endif; ?>
            <td><?= e($nota['disciplina']) ?></td>
            <td><?= e($nota['prova_1']) ?></td><td><?= e($nota['prova_2']) ?></td><td><?= e($nota['trabalho']) ?></td><td><?= e($nota['comportamental']) ?></td><td><?= e($nota['media_uc']) ?></td><td><?= badge($nota['status']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->