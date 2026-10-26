# RELATORIO FUNCIONARIOS FORM UNICO

## 1. Problema encontrado

O projeto ainda tinha formularios separados para cadastro de funcionarios:

* `FrmAdministrativo`
* `FrmCoordenacao`
* `FrmFuncionarioBase`

Esse fluxo nao era mais o desejado. A regra atual e usar somente `FrmFuncionarios` para cadastrar funcionarios administrativos e de coordenacao, escolhendo o tipo/perfil em um ComboBox.

## 2. Correcao aplicada

O menu do perfil Admin agora possui a opcao `Funcionarios`.

Essa opcao abre diretamente:

```text
FrmFuncionarios
```

O formulario `FrmFuncionarios` recebeu o ComboBox `cmbPerfilFuncionario`, criado no Designer, com os valores usados pelo sistema:

* `administrativo`
* `coordenacao`

Ao salvar, o perfil selecionado no ComboBox e gravado no campo `NivelAcesso` do usuario.

Na edicao, o perfil gravado e carregado de volta no ComboBox.

Na listagem, a coluna `NivelAcesso` recebeu o titulo `Tipo / Perfil`.

## 3. Formularios removidos

Foram removidos do projeto os formularios visuais:

* `FrmAdministrativo`
* `FrmCoordenacao`
* `FrmFuncionarioBase`

Arquivos removidos:

* `FlowAcademy/FrmAdministrativo.cs`
* `FlowAcademy/FrmAdministrativo.Designer.cs`
* `FlowAcademy/FrmAdministrativo.resx`
* `FlowAcademy/FrmCoordenacao.cs`
* `FlowAcademy/FrmCoordenacao.Designer.cs`
* `FlowAcademy/FrmCoordenacao.resx`
* `FlowAcademy/FrmFuncionarioBase.cs`
* `FlowAcademy/FrmFuncionarioBase.Designer.cs`
* `FlowAcademy/FrmFuncionarioBase.resx`

Tambem foram removidas as entradas correspondentes no `FlowAcademy.csproj.user`.

Foi feita busca por referencias a `FrmAdministrativo`, `FrmCoordenacao` e `FrmFuncionarioBase`; nao restaram referencias em codigo, Designer, `.csproj`, `.csproj.user` ou `.resx`.

## 4. Arquivos alterados

Arquivos alterados:

* `FlowAcademy/FrmPrincipal.cs`
* `FlowAcademy/FrmPrincipal.Designer.cs`
* `FlowAcademy/FrmFuncionarios.cs`
* `FlowAcademy/FrmFuncionarios.Designer.cs`
* `FlowAcademy/FlowAcademy.csproj.user`

Arquivo criado:

* `RELATORIO_FUNCIONARIOS_FORM_UNICO.md`

Arquivos removidos:

* `FlowAcademy/FrmAdministrativo.cs`
* `FlowAcademy/FrmAdministrativo.Designer.cs`
* `FlowAcademy/FrmAdministrativo.resx`
* `FlowAcademy/FrmCoordenacao.cs`
* `FlowAcademy/FrmCoordenacao.Designer.cs`
* `FlowAcademy/FrmCoordenacao.resx`
* `FlowAcademy/FrmFuncionarioBase.cs`
* `FlowAcademy/FrmFuncionarioBase.Designer.cs`
* `FlowAcademy/FrmFuncionarioBase.resx`

## 5. Banco de dados

Nao foi necessario alterar banco de dados ou procedures.

Foi usado o campo existente `Usuario.NivelAcesso`, que ja representa o perfil do usuario no sistema.

Os valores usados sao os mesmos ja existentes no codigo:

* `administrativo`
* `coordenacao`

## 6. O que nao foi alterado

Nao foram alterados os demais formularios do sistema.

Nao foram alterados:

* `FrmAluno`
* `FrmProfessor`
* `FrmCurso`
* `FrmDisciplina`
* `FrmTurma`
* `FrmMatricula`
* `FrmNota`
* `FrmFrequencia`
* `FrmPagamento`
* `FrmBoletimAluno`
* `FrmFrequenciaAluno`

Tambem nao foram alterados banco, procedures, fluxo de aluno, fluxo de professor, fluxo de notas, fluxo de frequencia, pagamentos, cursos, disciplinas, turmas ou matriculas.

## 7. Testes realizados

* Compilacao do projeto: executado `dotnet build FlowAcademy\FlowAcademy.csproj -p:NuGetAudit=false -v:minimal`.
* Resultado da compilacao: sucesso, 0 avisos e 0 erros.
* Designer de `FrmFuncionarios`: nao foi aberto manualmente no Visual Studio neste ambiente. A estrutura foi mantida com componentes fixos no `FrmFuncionarios.Designer.cs`.
* Menu Admin: validado por codigo. `btnFuncionarios` e exibido no perfil `admin` e abre `FrmFuncionarios` no painel principal.
* Cadastro com tipo Administrativo: validado por codigo. O valor `administrativo` e enviado para `Usuario.NivelAcesso`.
* Cadastro com tipo Coordenacao: validado por codigo. O valor `coordenacao` e enviado para `Usuario.NivelAcesso`.
* Edicao: validada por codigo. O valor de `Usuario.NivelAcesso` e carregado no `cmbPerfilFuncionario`.
* Formularios removidos: validado por busca. Nao restaram referencias quebradas para `FrmAdministrativo`, `FrmCoordenacao` ou `FrmFuncionarioBase`.
