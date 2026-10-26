<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Lista alunos cadastrados e exibe dados basicos para consulta administrativa.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin', 'coordenacao', 'administrativo'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Alunos';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'alunos';

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$alunos = buscarTodos(
    'SELECT a.*, u.nome, u.email, u.status AS status_usuario
     FROM alunos a
     JOIN usuarios u ON u.id_usuario = a.id_usuario
     ORDER BY u.nome'
);

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading">
  <div><span class="eyebrow">Alunos</span><h1>Alunos</h1><p>Consulta de alunos cadastrados.</p></div>
  <div class="actions"><a class="btn primary" href="cadastro_aluno.php">Novo aluno</a></div>
</section>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Lista de alunos</h2></div>
  <div class="table-wrap">
<!-- RETORNO - Tabela apresenta ao usuario os dados consultados no banco. -->
    <table id="tabela-principal">
      <thead><tr><th>Nome</th><th>Matricula</th><th>CPF</th><th>Telefone</th><th>Status</th><th>Acoes</th></tr></thead>
      <tbody>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
        <?php if (empty($alunos)): ?><tr><td colspan="6">Nenhum aluno cadastrado.</td></tr><?php endif; ?>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
        <?php foreach ($alunos as $aluno): ?>
          <tr>
            <td><strong><?= e($aluno['nome']) ?></strong><br><span class="muted"><?= e($aluno['email']) ?></span></td>
            <td><?= e($aluno['matricula']) ?></td>
            <td><?= e($aluno['cpf']) ?></td>
            <td><?= e($aluno['telefone']) ?></td>
            <td><?= badge($aluno['status_academico']) ?></td>
            <td class="table-actions">
              <a class="btn ghost" href="ver_aluno.php?id=<?= e($aluno['id_aluno']) ?>">Ver</a>
              <a class="btn ghost" href="cadastro_aluno.php?editar=<?= e($aluno['id_aluno']) ?>">Editar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->