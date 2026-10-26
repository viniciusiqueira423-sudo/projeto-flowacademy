# RELATORIO_CORRECAO_PERMISSAO_ALUNO

## 1. Problema encontrado

O usuario com perfil `aluno` estava recebendo acesso aos mesmos botoes de `Notas` e `Frequencia` usados para lancamento.

Isso permitia que o aluno abrisse `FrmNota` e `FrmFrequencia`, que sao telas de lancamento/cadastro/edicao/exclusao, e nao telas de consulta.

## 2. Correcao aplicada

No `FrmPrincipal`, o perfil `aluno` deixou de exibir os menus de lancamento `btnNotas` e `btnFrequencia`.

Agora, para aluno, o sistema exibe apenas o menu `Consultas`, reaproveitando o botao existente `btnAlunos` e abrindo `FrmAlunoConsultas`.

Tambem foi adicionada protecao simples em:

* `FrmNota`
* `FrmFrequencia`

Se o perfil logado for `aluno`, essas telas exibem mensagem de acesso nao permitido e fecham.

Admin e professor nao tiveram suas permissoes alteradas.

## 3. Formulario vazio

Foi criada a classe visual `FrmAlunoConsultas` como tela simples de menu do aluno.

Ela possui componentes no Designer:

* `lblTitulo`
* `btnBoletim`
* `btnFrequencia`

Os botoes abrem:

* `FrmBoletimAluno`
* `FrmFrequenciaAluno`

Os formularios de consulta existentes continuam filtrando os dados pelo usuario logado usando `Sessao.IdUsuario`, para mostrar apenas dados do aluno vinculado ao usuario da sessao.

## 4. Arquivos alterados

* `FlowAcademy/FrmPrincipal.cs`
* `FlowAcademy/FrmNota.cs`
* `FlowAcademy/FrmFrequencia.cs`
* `FlowAcademy/FrmAlunoConsultas.cs`
* `FlowAcademy/FrmAlunoConsultas.Designer.cs`

## 5. Testes realizados

Foi executado:

```powershell
dotnet build FlowAcademy.sln --no-restore
```

Resultado:

* compilacao com sucesso
* 0 erros
* 0 avisos

Validacoes feitas no codigo:

* login admin: permissoes administrativas foram mantidas no `FrmPrincipal`
* login professor: acesso a lancamento de notas/frequencia foi mantido
* login aluno: menu passou a abrir apenas `FrmAlunoConsultas`
* consulta de boletim: botao `btnBoletim` abre `FrmBoletimAluno`
* consulta de frequencia: botao `btnFrequencia` abre `FrmFrequenciaAluno`

Nao foi feita execucao manual com login real e banco MySQL nesta etapa.
