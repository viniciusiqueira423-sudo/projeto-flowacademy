<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Cria ou edita cursos e suas unidades curriculares.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// INÍCIO - Carrega dependencias compartilhadas antes de executar esta pagina.
require_once __DIR__ . '/../includes/funcoes.php';
// VALIDAÇÃO - Garante que somente perfis autorizados acessem este arquivo.
exigirPerfil(['admin', 'coordenacao'], '../');

// PROCESSAMENTO - Variavel $base guarda estado ou dados usados nas proximas etapas.
$base = '../';
// PROCESSAMENTO - Variavel $titulo guarda estado ou dados usados nas proximas etapas.
$titulo = 'Novo Curso';
// PROCESSAMENTO - Variavel $ativo guarda estado ou dados usados nas proximas etapas.
$ativo = 'novo_curso';
// PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
$erro = '';
// PROCESSAMENTO - Variavel $editando guarda estado ou dados usados nas proximas etapas.
$editando = null;

// PROCESSAMENTO - Detecta envio de formulario e inicia o fluxo de gravacao.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('acao') === 'salvar') {
    // TRATAMENTO DE ERROS - Protege operacoes de banco/validacao para exibir mensagem amigavel em caso de falha.
    try {
        // PROCESSAMENTO - Variavel $idCurso guarda estado ou dados usados nas proximas etapas.
        $idCurso = (int) post('id_curso');
        // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
        if ($idCurso > 0) {
            // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
            executar(
                'UPDATE cursos SET nome = :nome, descricao = :descricao, carga_horaria = :carga_horaria, status = :status WHERE id_curso = :id_curso',
                [':nome' => post('nome'), ':descricao' => post('descricao'), ':carga_horaria' => (int) post('carga_horaria'), ':status' => post('status'), ':id_curso' => $idCurso]
            );
            // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
            flash('success', 'Curso atualizado com sucesso.');
        } else {
            // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
            executar(
                'INSERT INTO cursos (nome, descricao, carga_horaria, status) VALUES (:nome, :descricao, :carga_horaria, :status)',
                [':nome' => post('nome'), ':descricao' => post('descricao'), ':carga_horaria' => (int) post('carga_horaria'), ':status' => post('status')]
            );
            // PROCESSAMENTO - Variavel $idCurso guarda estado ou dados usados nas proximas etapas.
            $idCurso = $pdo->lastInsertId();
            // PROCESSAMENTO - Variavel $nomesUc guarda estado ou dados usados nas proximas etapas.
            $nomesUc = $_POST['uc_nome'] ?? [];
            // PROCESSAMENTO - Variavel $cargasUc guarda estado ou dados usados nas proximas etapas.
            $cargasUc = $_POST['uc_carga'] ?? [];
            // PROCESSAMENTO - Percorre a lista para montar dados ou renderizar itens na tela.
            foreach ($nomesUc as $i => $nomeUc) {
                // PROCESSAMENTO - Variavel $nomeUc guarda estado ou dados usados nas proximas etapas.
                $nomeUc = trim((string) $nomeUc);
                // VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
                if ($nomeUc === '') { continue; }
                // CONSULTA AO BANCO - Executa comando de escrita/atualizacao usando parametros preparados.
                executar(
                    'INSERT INTO disciplinas (id_curso, nome, carga_horaria) VALUES (:id_curso, :nome, :carga_horaria)',
                    [':id_curso' => $idCurso, ':nome' => $nomeUc, ':carga_horaria' => (int) ($cargasUc[$i] ?? 0)]
                );
            }
            // RETORNO - Prepara mensagem temporaria para informar o resultado ao usuario.
            flash('success', 'Curso cadastrado com sucesso.');
        }
        // PROCESSAMENTO - Registra auditoria da acao realizada pelo usuario.
        registrarLog('Salvou curso');
        // RETORNO - Redireciona apos concluir o fluxo e evita reenvio de formulario.
        redirecionar('cursos.php');
    } catch (Throwable $erroSql) {
        // PROCESSAMENTO - Variavel $erro guarda estado ou dados usados nas proximas etapas.
        $erro = $erroSql->getMessage();
    }
}

// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if (getValor('editar') !== '') {
    // CONSULTA AO BANCO - Busca um unico registro para validar ou preencher a tela.
    $editando = buscarUm('SELECT * FROM cursos WHERE id_curso = :id', [':id' => (int) getValor('editar')]);
}

// PROCESSAMENTO - Variavel $ucsExistentes guarda estado ou dados usados nas proximas etapas.
$ucsExistentes = [];
// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if ($editando) {
    // CONSULTA AO BANCO - Busca uma colecao de registros usando SQL preparado.
    $ucsExistentes = buscarTodos('SELECT nome, carga_horaria FROM disciplinas WHERE id_curso = :id ORDER BY nome', [':id' => $editando['id_curso']]);
}

// RETORNO - A partir daqui a pagina comeca a renderizar o layout HTML comum.
require __DIR__ . '/../includes/header.php';
?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="page-heading"><div><span class="eyebrow">Cadastro</span><h1><?= $editando ? 'Editar Curso' : 'Novo Curso' ?></h1><p>Cadastre o curso e informe a carga horaria de cada unidade curricular.</p></div></section>
<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
<?php if ($erro): ?><div class="alert danger"><span class="alert-marker"></span><div><strong>Erro</strong><span class="muted"><?= e($erro) ?></span></div></div><?php endif; ?>

<!-- INÍCIO - Secao visual que agrupa informacoes ou formulario desta parte da pagina. -->
<section class="panel">
  <div class="panel-header"><h2>Dados do curso</h2></div>
  <div class="panel-body">
<!-- PROCESSAMENTO - Formulario envia dados para o proprio PHP tratar no topo do arquivo. -->
    <form class="form-grid" method="post">
      <input type="hidden" name="acao" value="salvar">
      <input type="hidden" name="id_curso" value="<?= e($editando['id_curso'] ?? '') ?>">

      <label class="field span-2"><span>Nome</span><input class="control" name="nome" value="<?= e($editando['nome'] ?? '') ?>" required></label>
      <label class="field"><span>Carga horaria</span><input class="control" type="number" name="carga_horaria" value="<?= e($editando['carga_horaria'] ?? '') ?>" required></label>
      <label class="field"><span>Status</span>
        <select class="select" name="status">
          <option value="ativo" <?= ($editando['status'] ?? 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
          <option value="inativo" <?= ($editando['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
        </select>
      </label>
      <label class="field span-2"><span>Descricao</span><textarea class="textarea" name="descricao"><?= e($editando['descricao'] ?? '') ?></textarea></label>

<!-- VALIDAÇÃO - Bloco condicional da camada de apresentacao. -->
      <?php if (!$editando): ?>
        <div class="span-2 stack uc-editor" data-uc-editor data-uc-template="#uc-template">
          <span class="field-label"><strong>Unidades curriculares</strong></span>
          <div data-uc-list>
            <div class="uc-row" data-uc-row>
              <label class="field"><span>Nome da UC</span><input class="control" name="uc_nome[]" placeholder="Ex.: Banco de dados"></label>
              <label class="field"><span>Carga horaria</span><input class="control" type="number" name="uc_carga[]" placeholder="Horas"></label>
              <button class="btn ghost" type="button" data-remove-uc>Remover</button>
            </div>
          </div>
          <button class="btn ghost" type="button" data-add-uc>Adicionar UC</button>
        </div>

        <template id="uc-template">
          <div class="uc-row" data-uc-row>
            <label class="field"><span>Nome da UC</span><input class="control" name="uc_nome[]" placeholder="Ex.: Banco de dados"></label>
            <label class="field"><span>Carga horaria</span><input class="control" type="number" name="uc_carga[]" placeholder="Horas"></label>
            <button class="btn ghost" type="button" data-remove-uc>Remover</button>
          </div>
        </template>
      <?php else: ?>
        <div class="span-2 panel">
          <div class="panel-header"><h2>Unidades curriculares cadastradas</h2></div>
          <div class="table-wrap">
<!-- RETORNO - Tabela apresenta ao usuario os dados consultados no banco. -->
            <table><thead><tr><th>UC</th><th>Carga</th></tr></thead><tbody>
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
              <?php foreach ($ucsExistentes as $uc): ?><tr><td><?= e($uc['nome']) ?></td><td><?= e($uc['carga_horaria']) ?>h</td></tr><?php endforeach; ?>
            </tbody></table>
          </div>
        </div>
      <?php endif; ?>

      <div class="actions span-2"><button class="btn primary" type="submit">Salvar curso</button><a class="btn ghost" href="cursos.php">Voltar</a></div>
    </form>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->