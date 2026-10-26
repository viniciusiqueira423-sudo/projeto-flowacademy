<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Cria ou edita pagamentos relacionados a matriculas.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin', 'administrativo'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Novo Pagamento';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'novo_pagamento';
// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';
// PROCESSAMENTO - Variavel $editando guarda estado ou dados usados nas proximas etapas.
$editando = null;

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('acao') === 'salvar') {
    // TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
    try {
        // PROCESSAMENTO - Variavel $idPagamento guarda estado ou dados usados nas proximas etapas.
        $idPagamento = (int) post('id_pagamento');
        // PROCESSAMENTO - Variavel $dadosPagamento guarda estado ou dados usados nas proximas etapas.
        $dadosPagamento = [
            ':id_aluno' => (int) post('id_aluno'),
            ':valor' => (float) str_replace(',', '.', post('valor')),
            ':vencimento' => post('vencimento'),
            ':status' => post('status', 'pendente'),
        ];

        // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
        if ($idPagamento > 0) {
            // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
            executar(
                'UPDATE pagamentos SET id_aluno = :id_aluno, valor = :valor, vencimento = :vencimento, status = :status WHERE id_pagamento = :id_pagamento',
                $dadosPagamento + [':id_pagamento' => $idPagamento]
            );
            // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
            flash('success', 'Pagamento atualizado com sucesso.');
        } else {
            // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
            executar(
                'INSERT INTO pagamentos (id_aluno, valor, vencimento, status) VALUES (:id_aluno, :valor, :vencimento, :status)',
                $dadosPagamento
            );
            // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
            flash('success', 'Pagamento cadastrado com sucesso.');
        }
        // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
        registrarLog('Salvou pagamento');
        // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
        redirecionar('pagamentos.php');
    } catch (Throwable $erroSql) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = $erroSql->getMessage();
    }
}

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if (getValor('editar') !== '') {
    // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
    $editando = buscarUm('SELECT * FROM pagamentos WHERE id_pagamento = :id', [':id' => (int) getValor('editar')]);
}

// CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
$alunos = buscarTodos(
    'SELECT a.id_aluno, a.matricula, u.nome
     FROM alunos a
     JOIN usuarios u ON u.id_usuario = a.id_usuario
     ORDER BY u.nome'
);

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading"><div><span class="eyebrow">Administrativo</span><h1><?= $editando ? 'Editar Pagamento' : 'Novo Pagamento' ?></h1><p>Cadastre cobrancas vinculadas ao aluno.</p></div></section>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
<?php if ($erro): ?><div class="alert danger"><span class="alert-marker"></span><div><strong>Erro</strong><span class="muted"><?= e($erro) ?></span></div></div><?php endif; ?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Dados do pagamento</h2></div>
  <div class="panel-body">
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
    <form class="form-grid" method="post">
      <input type="hidden" name="acao" value="salvar">
      <input type="hidden" name="id_pagamento" value="<?= e($editando['id_pagamento'] ?? '') ?>">
      <label class="field span-2"><span>Aluno</span>
        <select class="select" name="id_aluno" required>
          <option value="">Selecione</option>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach ($alunos as $aluno): ?><option value="<?= e($aluno['id_aluno']) ?>" <?= (int) ($editando['id_aluno'] ?? 0) === (int) $aluno['id_aluno'] ? 'selected' : '' ?>><?= e($aluno['nome']) ?> - <?= e($aluno['matricula']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Valor</span><input class="control" name="valor" value="<?= e($editando['valor'] ?? '') ?>" required></label>
      <label class="field"><span>Vencimento</span><input class="control" type="date" name="vencimento" value="<?= e($editando['vencimento'] ?? '') ?>" required></label>
      <label class="field"><span>Status</span>
        <select class="select" name="status">
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
          <?php foreach (['pendente', 'pago', 'atrasado', 'cancelado'] as $status): ?>
            <option value="<?= e($status) ?>" <?= ($editando['status'] ?? 'pendente') === $status ? 'selected' : '' ?>><?= e(textoStatus($status)) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <div class="actions span-2"><button class="btn primary" type="submit">Salvar pagamento</button><a class="btn ghost" href="pagamentos.php">Voltar</a></div>
    </form>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->