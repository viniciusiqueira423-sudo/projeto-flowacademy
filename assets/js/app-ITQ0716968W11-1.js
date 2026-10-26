// INÍCIO - DOCUMENTACAO DE ESTUDO
// Proposito do arquivo: Concentra interacoes de interface: filtros, mascaras, menus, toast e barras de progresso.
// Fluxo geral: aguardar o DOM carregar, preparar utilitarios, registrar eventos e atualizar a interface.
// INÍCIO - Registra um ouvinte de evento do navegador para coordenar interacoes da tela.
document.addEventListener('DOMContentLoaded', () => {
  // PROCESSAMENTO - Atalho para buscar um unico elemento no DOM.
  const $ = (selector, root = document) => root.querySelector(selector);
  // PROCESSAMENTO - Atalho para buscar varios elementos e transforma-los em array.
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));
  const body = document.body;
  const toast = $('[data-toast-root]');
  let toastTimer;

  // PROCESSAMENTO - Normaliza texto para filtros sem diferenciar maiusculas ou acentos.
  const normalize = (value) =>
    String(value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

  // PROCESSAMENTO - Mantem somente numeros para aplicar mascaras de CPF/telefone.
  const digits = (value, limit) => String(value || '').replace(/\D/g, '').slice(0, limit);

  // RETORNO - Exibe feedback visual temporario ao usuario.
  function showToast(message) {
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (!toast) return;
    toast.textContent = message || 'Acao concluida.';
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
  }

  // PROCESSAMENTO - Aplica mascara de digitacao conforme o tipo definido no HTML.
  function mask(input) {
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (input.dataset.mask === 'cpf') {
      input.value = digits(input.value, 11)
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
      // RETORNO - Encerra este bloco quando nao ha elemento ou dado suficiente para continuar.
      return;
    }

    const value = digits(input.value, 11);
    input.value = value.length <= 10
      ? value.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2')
      : value.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
  }

  // PROCESSAMENTO - Filtra alunos e UCs de acordo com a turma selecionada.
  function applyTurmaFilter(turmaSelect) {
    const turmaId = turmaSelect.value;
    // PROCESSAMENTO - Itera pelos elementos encontrados para aplicar comportamento de forma uniforme.
    [turmaSelect.dataset.filterStudents, turmaSelect.dataset.filterUcs].filter(Boolean).forEach((selector) => {
      const select = $(selector);
      // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
      if (!select) return;

      // PROCESSAMENTO - Itera pelos elementos encontrados para aplicar comportamento de forma uniforme.
      $$('option', select).forEach((option) => {
        // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
        if (!option.value) {
          option.hidden = false;
          option.disabled = false;
          // RETORNO - Encerra este bloco quando nao ha elemento ou dado suficiente para continuar.
          return;
        }

        const visible = turmaId && option.dataset.turma === turmaId && option.dataset.searchHidden !== '1';
        option.hidden = !visible;
        option.disabled = !visible;
      });

      const selected = select.selectedOptions[0];
      // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
      if (!turmaId || (selected && selected.value && selected.dataset.turma !== turmaId)) {
        select.value = '';
      }
    });
  }

  // PROCESSAMENTO - Marca opcoes de aluno como ocultas quando nao batem com a busca.
  function applyStudentSearch(input) {
    const select = $(input.dataset.studentSearch);
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (!select) return;

    const query = normalize(input.value);
    // PROCESSAMENTO - Itera pelos elementos encontrados para aplicar comportamento de forma uniforme.
    $$('option', select).forEach((option) => {
      // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
      if (!option.value) return;
      const text = normalize(`${option.dataset.search || ''} ${option.textContent}`);
      option.dataset.searchHidden = query && !text.includes(query) ? '1' : '0';
    });

    const turmaSelect = $(input.dataset.turmaSource);
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (turmaSelect) applyTurmaFilter(turmaSelect);
  }

  // INÍCIO - Registra um ouvinte de evento do navegador para coordenar interacoes da tela.
  document.addEventListener('click', (event) => {
    const target = event.target;

    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (target.closest('.js-sidebar-toggle')) body.classList.toggle('sidebar-open');
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (target.closest('.js-sidebar-close')) body.classList.remove('sidebar-open');

    const passwordButton = target.closest('[data-password-toggle]');
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (passwordButton) {
      const input = $(passwordButton.dataset.passwordToggle);
      // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
      if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
        passwordButton.textContent = input.type === 'password' ? 'Mostrar' : 'Ocultar';
      }
    }

    const addUcButton = target.closest('[data-add-uc]');
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (addUcButton) {
      const editor = addUcButton.closest('[data-uc-editor]');
      // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
      if (!editor) return;

      const list = $('[data-uc-list]', editor);
      const template = $(editor.dataset.ucTemplate);
      // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
      if (list && template) {
        list.appendChild(template.content.cloneNode(true));
        $('[data-uc-row]:last-child input', list)?.focus();
      }
    }

    const removeUcButton = target.closest('[data-remove-uc]');
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (removeUcButton) {
      const list = removeUcButton.closest('[data-uc-list]');
      const row = removeUcButton.closest('[data-uc-row]');
      // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
      if (!list || !row) return;

      const rows = $$('[data-uc-row]', list);
      // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
      if (rows.length <= 1) $$('input', row).forEach((input) => { input.value = ''; });
      else row.remove();
    }

    const toastButton = target.closest('[data-toast]');
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (toastButton) showToast(toastButton.dataset.toast);

    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (target.closest('[data-print]')) window.print();
  });

  // INÍCIO - Registra um ouvinte de evento do navegador para coordenar interacoes da tela.
  document.addEventListener('input', (event) => {
    const input = event.target;

    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (input.matches('[data-mask]')) mask(input);
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (input.matches('[data-student-search]')) applyStudentSearch(input);
  });

  // PROCESSAMENTO - Itera pelos elementos encontrados para aplicar comportamento de forma uniforme.
  $$('[data-turma-filter]').forEach((select) => {
    select.addEventListener('change', () => applyTurmaFilter(select));
    applyTurmaFilter(select);
  });

  // PROCESSAMENTO - Itera pelos elementos encontrados para aplicar comportamento de forma uniforme.
  $$('[data-student-search]').forEach(applyStudentSearch);

  // PROCESSAMENTO - Itera pelos elementos encontrados para aplicar comportamento de forma uniforme.
  $$('[data-table-filter]').forEach((input) => {
    const table = $(input.dataset.tableFilter);
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (!table) return;

    const rows = $$('tbody tr', table);
    input.addEventListener('input', () => {
      const query = normalize(input.value);
      // PROCESSAMENTO - Itera pelos elementos encontrados para aplicar comportamento de forma uniforme.
      rows.forEach((row) => {
        row.style.display = normalize(row.textContent).includes(query) ? '' : 'none';
      });
    });
  });

  // PROCESSAMENTO - Itera pelos elementos encontrados para aplicar comportamento de forma uniforme.
  $$('[data-progress]').forEach((bar) => {
    const fill = $('span', bar);
    const value = Math.max(0, Math.min(100, Number(bar.dataset.progress) || 0));
    // VALIDAÇÃO - Condicao que decide se a acao do usuario exige alguma atualizacao na interface.
    if (fill) requestAnimationFrame(() => { fill.style.width = `${value}%`; });
  });
});

// FIM - Final do arquivo documentado para estudo.