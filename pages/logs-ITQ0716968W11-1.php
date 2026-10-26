<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Consulta o historico de acoes registradas no sistema.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Logs';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'logs';

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$logs = buscarTodos(
    'SELECT l.*, u.nome, u.email, u.perfil
     FROM logs l
     JOIN usuarios u ON u.id_usuario = l.id_usuario
     ORDER BY l.data_evento DESC
     LIMIT 200'
);

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading"><div><span class="eyebrow">Logs</span><h1>Logs</h1><p>Historico das principais acoes realizadas no sistema.</p></div></section>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Eventos registrados</h2></div>
  <div class="table-wrap">
<!-- RETORNO - Tabela apresenta ao usuario os dados consultados no banco. -->
    <table id="tabela-principal">
      <thead><tr><th>Data</th><th>Usuario</th><th>E-mail</th><th>Perfil</th><th>Acao</th><th>IP</th></tr></thead>
      <tbody>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
        <?php if (empty($logs)): ?><tr><td colspan="6">Nenhum evento registrado.</td></tr><?php endif; ?>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
        <?php foreach ($logs as $log): ?>
          <tr>
            <td><?= e(dataBr($log['data_evento'])) ?></td><td><strong><?= e($log['nome']) ?></strong></td><td><?= e($log['email']) ?></td>
            <td><?= e(textoStatus($log['perfil'])) ?></td><td><?= e($log['acao']) ?></td><td><?= e($log['ip']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->