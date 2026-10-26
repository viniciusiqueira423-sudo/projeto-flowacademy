# RELATORIO DE ALTERACOES - FlowAcademy

## 1. Resumo geral

Foram feitos ajustes pequenos e diretos nos formularios administrativos e nos formularios usados pelo professor. O foco foi corrigir ComboBoxes, DataGridViews, pesquisas, filtros por turma e botoes, mantendo o padrao atual do projeto com Windows Forms, classes simples em `FlowAcademyClasses`, `Banco.Abrir()` e `MySql.Data`.

O projeto foi compilado com sucesso apos as alteracoes.

## 2. Arquivos alterados

* `FlowAcademy/FrmAluno.cs`
* `FlowAcademy/FrmProfessor.cs`
* `FlowAcademy/FrmCurso.cs`
* `FlowAcademy/FrmDisciplina.cs`
* `FlowAcademy/FrmTurma.cs`
* `FlowAcademy/FrmMatricula.cs`
* `FlowAcademy/FrmNota.cs`
* `FlowAcademy/FrmFrequencia.cs`
* `FlowAcademy/FrmPagamento.cs`
* `FlowAcademy/FrmPrincipal.cs`
* `FlowAcademy/FrmPrimeiroAcesso.cs`
* `FlowAcademyClasses/Aluno.cs`
* `FlowAcademyClasses/Professor.cs`
* `FlowAcademyClasses/Disciplina.cs`
* `FlowAcademyClasses/Turma.cs`
* `FlowAcademyClasses/Matricula.cs`
* `FlowAcademyClasses/Nota.cs`
* `FlowAcademyClasses/Frequencia.cs`
* `FlowAcademyClasses/Pagamento.cs`
* `FlowAcademyClasses/Usuario.cs`

## 3. Alteracoes por tela

### Alunos

* O grid passou a exibir o nome do usuario/aluno por meio de `NomeUsuario`.
* IDs de relacionamento e objetos internos foram ocultados do grid.
* O cadastro continua criando usuario com perfil `aluno`.
* Foram adicionados campos simples para data de nascimento e status academico.
* O salvar/editar agora usa `DataNascimento` e `StatusAcademico`.

### Professores

* O grid passou a exibir o nome do professor por meio de `NomeUsuario`.
* IDs de relacionamento e objetos internos foram ocultados.
* O cadastro continua criando usuario com perfil `professor`.
* A listagem de professores filtra usuarios do perfil `professor`.

### Cursos

* Foi adicionada uma lista simples de disciplinas vinculadas ao curso selecionado.
* A integracao mantem a estrutura existente: disciplina pertence a curso por `id_curso`.
* Nao foi criada tabela nova nem relacao complexa.

### Disciplinas

* O `cmbCurso` continua exibindo o nome do curso e usando `IdCurso` internamente.
* O grid passou a mostrar o nome do curso por meio de `NomeCurso`.
* A pesquisa continua funcionando por nome da disciplina e nome do curso.
* O ID do curso foi ocultado do grid.

### Turmas

* O ComboBox de professor mostra nome de professor/usuario e usa `IdProfessor`.
* A listagem de turma passou a trazer nome do curso e nome do professor.
* O grid oculta IDs de curso/professor e mostra curso/professor de forma legivel.
* A pesquisa foi ajustada para filtrar pelo codigo da turma.
* `nudCapacidade` foi ajustado para minimo 1 e a validacao impede capacidade invalida.

### Matriculas

* O `cmbAluno` passou a exibir o nome do aluno e manter `IdAluno` como valor interno.
* O grid mostra nome do aluno e codigo da turma.
* IDs de aluno/turma e objetos internos foram ocultados.
* A pesquisa foi ajustada para codigo/identificacao da matricula, codigo da turma e matricula do aluno.

### Notas

* Foi mantido o filtro por turma.
* Admin visualiza todas as turmas; professor visualiza somente turmas vinculadas ao seu usuario.
* Ao selecionar turma, aparecem apenas alunos matriculados naquela turma e disciplinas do curso da turma.
* O ComboBox visualmente mostra aluno/nome, usando `IdMatricula` internamente.
* O grid mostra matricula, aluno, disciplina e turma com nomes/codigos legiveis.
* IDs de matricula/disciplina e objetos internos foram ocultados.
* A pesquisa foi ajustada para nome do aluno.
* Botoes salvar, editar, excluir, cancelar, calcular e pesquisar possuem eventos.

### Frequencia

* Foi mantido o filtro por turma.
* Admin visualiza todas as turmas; professor visualiza somente turmas vinculadas ao seu usuario.
* Ao selecionar turma, aparecem apenas alunos matriculados naquela turma e disciplinas do curso da turma.
* Aluno e disciplina aparecem por nome nos ComboBoxes.
* O grid mostra matricula, aluno, disciplina e turma de forma legivel.
* IDs de matricula/disciplina e objetos internos foram ocultados.
* A pesquisa foi ajustada para nome do aluno.
* Botoes salvar, editar, excluir, cancelar, calcular e pesquisar possuem eventos.

### Pagamentos

* O grid passou a exibir `NomeAluno`.
* Foi adicionado campo simples de pesquisa por nome do aluno.
* O ID do aluno foi ocultado do grid.
* Cadastro, edicao, exclusao e cancelamento foram mantidos.

### Principal/Dashboard

* O acesso ao formulario de usuarios foi removido/ocultado do menu do perfil `admin`.
* O formulario `FrmUsuario` nao foi removido.
* Login, sessao e demais permissoes foram preservados.

### PrimeiroLogin / PrimeiroAcesso

* Foi encontrada a tela `FrmPrimeiroAcesso`.
* Foram ajustados apenas os `TabIndex` dos campos e botoes.
* A ordem ficou: nova senha, confirmar senha, salvar, cancelar.

## 4. Alteracoes no banco

Nao foram alteradas connection string, tabelas, procedures ou scripts SQL.

Foram alterados SQLs simples de listagem/pesquisa nas classes:

* `Aluno.ObterLista()` e `Aluno.ObterPorId()`
* `Professor.ObterLista()` e `Professor.ObterPorId()`
* `Disciplina.ObterLista()`
* `Turma.ObterLista()`
* `Matricula.ObterLista()`
* `Nota.ObterLista()`
* `Frequencia.ObterLista()`
* `Pagamento.ObterLista()`

Foram adicionados metodos simples de consulta:

* `Usuario.ObterListaPorPerfil(...)`
* `Professor.ObterPorUsuario(...)`
* `Disciplina.ObterListaPorCurso(...)`
* `Turma.ObterListaPorProfessor(...)`
* `Matricula.ObterListaPorTurma(...)`

As stored procedures de inserir, atualizar e excluir foram mantidas. Se o banco usado na maquina nao tiver as colunas ja esperadas pelo projeto (`data_nascimento`, `status_academico`, `id_curso` em disciplinas, etc.), sera necessario ajustar o banco para bater com as classes existentes.

## 5. Observacoes importantes

* O padrao simples do projeto foi mantido.
* Nao foram criadas camadas novas, repositories, services, DTOs, interfaces, Entity Framework ou Dapper.
* `FrmUsuario` foi mantido no projeto, apenas o acesso pelo dashboard admin foi ocultado.
* A integracao Curso/Disciplina foi mantida usando a relacao atual `disciplinas.id_curso`.
* Nao foram removidos formularios.
* Recomenda-se testar manualmente os formularios com dados reais no MySQL, principalmente as listagens que usam joins.
* O primeiro build precisou de acesso ao NuGet para restaurar pacotes; depois disso, `dotnet build FlowAcademy.sln --no-restore` compilou com sucesso.

## 6. Checklist de testes

* Login admin
* Cadastro de aluno
* Edicao e exclusao de aluno
* Cadastro de professor
* Edicao e exclusao de professor
* Cadastro de curso
* Cadastro de disciplina vinculada a curso
* Visualizacao de disciplinas no cadastro de curso
* Cadastro de turma
* Pesquisa de turma por codigo
* Matricula
* Pesquisa de matricula por codigo/identificacao
* Lancamento de nota como admin
* Lancamento de nota como professor
* Pesquisa de nota por nome do aluno
* Lancamento de frequencia como admin
* Lancamento de frequencia como professor
* Pesquisa de frequencia por nome do aluno
* Pagamento
* Pesquisa de pagamento por nome do aluno
* Botoes editar, cancelar e excluir nos formularios alterados
