<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Lista cursos, disciplinas vinculadas e permite alternar status.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin', 'coordenacao'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Cursos';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'cursos';
// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('acao') === 'excluir') {
    // PROCESSAMENTO - Variavel $idCurso guarda estado ou dados usados nas proximas etapas.
    $idCurso = (int) post('id_curso');
    // PROCESSAMENTO - Variavel $turmasVinculadas guarda estado ou dados usados nas proximas etapas.
    $turmasVinculadas = contar('SELECT COUNT(*) AS total FROM turmas WHERE id_curso = :id', [':id' => $idCurso]);

    // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
    if ($turmasVinculadas > 0) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = 'Nao e possivel excluir este curso porque existem turmas vinculadas a ele.';
    } else {
        // TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
        try {
            // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
            executar('DELETE FROM cursos WHERE id_curso = :id_curso', [':id_curso' => $idCurso]);
            // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
            registrarLog('Excluiu curso');
            // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
            flash('success', 'Curso excluido com sucesso.');
            // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
            redirecionar('cursos.php');
        } catch (PDOException $erroSql) {
            // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
            $erro = $erroSql->getCode() === '23000'
                ? 'Nao e possivel excluir este curso porque existem registros vinculados a ele.'
                : 'Nao foi possivel excluir o curso. Tente novamente.';
        }
    }
}

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$cursos = buscarTodos(
    'SELECT c.*, COUNT(d.id_disciplina) AS total_ucs, COALESCE(SUM(d.carga_horaria), 0) AS carga_ucs
     FROM cursos c
     LEFT JOIN disciplinas d ON d.id_curso = c.id_curso
     GROUP BY c.id_curso
     ORDER BY c.nome'
);

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading">
  <div><span class="eyebrow">Cursos</span><h1>Cursos</h1><p>Lista de cursos cadastrados no banco.</p></div>
  <div class="actions"><a class="btn primary" href="novo_curso.php">Novo curso</a></div>
</section>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
<?php if ($erro): ?><div class="alert danger"><span class="alert-marker"></span><div><strong>Erro</strong><span class="muted"><?= e($erro) ?></span></div></div><?php endif; ?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Cursos cadastrados</h2></div>
  <div class="table-wrap">
<!-- RETORNO - Tabela apresenta ao usuario os dados consultados no banco. -->
    <table id="tabela-principal">
      <thead><tr><th>Curso</th><th>Carga do curso</th><th>UCs</th><th>Horas nas UCs</th><th>Status</th><th>Descricao</th><th>Acoes</th></tr></thead>
      <tbody>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
        <?php if (empty($cursos)): ?><tr><td colspan="7">Nenhum curso cadastrado.</td></tr><?php endif; ?>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
        <?php foreach ($cursos as $curso): ?>
          <tr>
            <td><?= e($curso['nome']) ?></td>
            <td><?= e($curso['carga_horaria']) ?>h</td>
            <td><?= e($curso['total_ucs']) ?></td>
            <td><?= e($curso['carga_ucs']) ?>h</td>
            <td><?= badge($curso['status']) ?></td>
            <td><?= e($curso['descricao']) ?></td>
            <td class="table-actions">
              <a class="btn ghost" href="novo_curso.php?editar=<?= e($curso['id_curso']) ?>">Editar</a>
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
              <form method="post" onsubmit="return confirm('Excluir curso?')"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id_curso" value="<?= e($curso['id_curso']) ?>"><button class="btn danger" type="submit">Excluir</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->