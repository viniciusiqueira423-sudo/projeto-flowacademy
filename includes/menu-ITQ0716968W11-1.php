<?php

// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Renderiza a navegacao lateral conforme o perfil ativo do usuario.
// Fluxo geral: carregar dependencias, validar permissao, processar entradas, consultar o banco e renderizar a tela.
// Observacao: foram adicionados somente comentarios; nenhuma regra de negocio foi alterada.

// PROCESSAMENTO - Variavel $usuarioMenu guarda estado ou dados usados nas proximas etapas.
$usuarioMenu = usuarioLogado();
// PROCESSAMENTO - Variavel $perfilReal guarda estado ou dados usados nas proximas etapas.
$perfilReal = $usuarioMenu['perfil'] ?? '';
// PROCESSAMENTO - Variavel $perfilMenu guarda estado ou dados usados nas proximas etapas.
$perfilMenu = perfilAtivo();

// Cada perfil enxerga apenas os grupos e links definidos para ele no manual.
// PROCESSAMENTO - Variavel $gruposPorPerfil guarda estado ou dados usados nas proximas etapas.
$gruposPorPerfil = [
    'aluno' => [
        'ALUNO' => [
            ['dashboard', 'Dashboard', 'dashboard.php'],
            ['boletim', 'Boletim', 'pages/boletim.php'],
            ['frequencias', 'Frequencia', 'pages/frequencias.php'],
        ],
    ],
    'professor' => [
        'PROFESSOR' => [
            ['dashboard', 'Dashboard', 'dashboard.php'],
            ['notas', 'Lancar notas', 'pages/notas.php'],
            ['frequencias', 'Registrar frequencia', 'pages/frequencias.php'],
        ],
    ],
    'coordenacao' => [
        'COORDENACAO' => [
            ['dashboard', 'Dashboard', 'dashboard.php'],
            ['cursos', 'Cursos', 'pages/cursos.php'],
            ['novo_curso', 'Novo curso', 'pages/novo_curso.php'],
            ['turmas', 'Turmas', 'pages/turmas.php'],
            ['nova_turma', 'Nova turma', 'pages/nova_turma.php'],
        ],
    ],
    'admin' => [
        'ADMIN' => [
            ['dashboard', 'Dashboard', 'dashboard.php'],
            ['cadastro_coordenacao', 'Cadastrar coordenacao', 'pages/cadastro_coordenacao.php'],
            ['cadastro_administrativo', 'Cadastrar administrativo', 'pages/cadastro_administrativo.php'],
            ['logs', 'Logs', 'pages/logs.php'],
        ],
    ],
    'administrativo' => [
        'ADMINISTRATIVO' => [
            ['dashboard', 'Dashboard', 'dashboard.php'],
            ['alunos', 'Alunos', 'pages/alunos.php'],
            ['cadastro_aluno', 'Cadastro de aluno', 'pages/cadastro_aluno.php'],
            ['matriculas', 'Matricula', 'pages/matriculas.php'],
            ['pagamentos', 'Pagamentos', 'pages/pagamentos.php'],
            ['novo_pagamento', 'Novo pagamento', 'pages/novo_pagamento.php'],
        ],
    ],
];

// O grupo exibido segue o PERFIL ATIVO (modo de visualizacao), nao o perfil real.
// Assim, quando o Admin "assume" a visao de Coordenacao ou Administrativo, o menu
// lateral fica identico ao daquele perfil, com os mesmos links funcionando.
// PROCESSAMENTO - Variavel $grupos guarda estado ou dados usados nas proximas etapas.
$grupos = $gruposPorPerfil[$perfilMenu] ?? [];

// O atalho PERFIS so existe para quem realmente esta logado como admin, e fica
// sempre disponivel (mesmo dentro do modo de visualizacao) para poder alternar
// ou voltar para a propria area do admin.
// VALIDAÇÃO - Condicao que direciona o fluxo conforme dados, perfil ou estado atual.
if ($perfilReal === 'admin') {
    $grupos['PERFIS'] = [
        ['perfil_admin', 'Admin', 'dashboard.php?ver=admin'],
        ['perfil_coordenacao', 'Coordenacao', 'dashboard.php?ver=coordenacao'],
        ['perfil_administrativo', 'Administrativo', 'dashboard.php?ver=administrativo'],
    ];
}
?>

<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
<?php foreach ($grupos as $tituloGrupo => $links): ?>
  <nav class="nav-section nav-group">
    <p class="nav-title"><?= e($tituloGrupo) ?></p>
    <ul class="nav-list">
<!-- PROCESSAMENTO - Repeticao usada para renderizar cada item retornado pelas consultas. -->
      <?php foreach ($links as $link): ?>
        <?php
          // PROCESSAMENTO - Variavel $ehAtalhoPerfil guarda estado ou dados usados nas proximas etapas.
          $ehAtalhoPerfil = strpos($link[0], 'perfil_') === 0;
          // PROCESSAMENTO - Variavel $estaAtivo guarda estado ou dados usados nas proximas etapas.
          $estaAtivo = $ehAtalhoPerfil
              ? $link[0] === 'perfil_' . $perfilMenu
              : ($ativo ?? '') === $link[0];
        ?>
        <li>
          <a class="nav-link <?= $estaAtivo ? 'active' : '' ?>" href="<?= e(($base ?? '') . $link[2]) ?>">
            <span class="nav-dot"></span><?= e($link[1]) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>
<?php endforeach; ?>

<!-- FIM - Final do arquivo documentado para estudo. -->