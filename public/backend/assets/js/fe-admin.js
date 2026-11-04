/*
 * Forward Edge admin behaviour. Replaces the NiceAdmin main.js.
 *  - sidebar drawer (toggle, outside click, Escape)
 *  - tooltips
 *  - feConfirm(): a real confirm dialog that says what will be lost
 *  - [data-confirm] on a <form>: confirm before submitting
 *  - [data-unsaved] on a <form>: warn before leaving with unsaved edits
 */
(function () {
  'use strict';

  var body = document.body;
  var desktop = window.matchMedia('(min-width: 1200px)');

  /* ---------- Sidebar ---------- */
  document.addEventListener('click', function (e) {
    var toggle = e.target.closest('.toggle-sidebar-btn');
    if (toggle) {
      body.classList.toggle('toggle-sidebar');
      toggle.setAttribute('aria-expanded', body.classList.contains('toggle-sidebar') ? 'true' : 'false');
      return;
    }
    // Below desktop the sidebar is a drawer: a tap outside it, or on a link inside it, closes it.
    if (!desktop.matches && body.classList.contains('toggle-sidebar') && !e.target.closest('#sidebar')) {
      body.classList.remove('toggle-sidebar');
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !desktop.matches && body.classList.contains('toggle-sidebar')) {
      body.classList.remove('toggle-sidebar');
      var t = document.querySelector('.toggle-sidebar-btn');
      if (t) t.focus();
    }
  });

  /* ---------- Tooltips ---------- */
  if (window.bootstrap && bootstrap.Tooltip) {
    [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(function (el) {
      new bootstrap.Tooltip(el);
    });
  }

  /* ---------- Confirm dialog ---------- */
  var dialog, resolver, lastFocus;

  function build() {
    if (dialog) return dialog;
    dialog = document.createElement('div');
    dialog.className = 'modal fade fe-confirm';
    dialog.tabIndex = -1;
    dialog.setAttribute('role', 'alertdialog');
    dialog.setAttribute('aria-modal', 'true');
    dialog.setAttribute('aria-labelledby', 'feConfirmTitle');
    dialog.setAttribute('aria-describedby', 'feConfirmBody');
    dialog.innerHTML =
      '<div class="modal-dialog modal-dialog-centered"><div class="modal-content">' +
      '<div class="modal-body fe-confirm-body">' +
      '<h2 class="fe-confirm-title" id="feConfirmTitle"></h2>' +
      '<p class="fe-confirm-text" id="feConfirmBody"></p>' +
      '</div>' +
      '<div class="modal-footer fe-confirm-foot">' +
      '<button type="button" class="btn btn-outline-secondary" data-fe-cancel>Cancel</button>' +
      '<button type="button" class="btn" data-fe-ok></button>' +
      '</div></div></div>';
    document.body.appendChild(dialog);

    dialog.querySelector('[data-fe-cancel]').addEventListener('click', function () { settle(false); });
    dialog.querySelector('[data-fe-ok]').addEventListener('click', function () { settle(true); });
    dialog.addEventListener('hidden.bs.modal', function () {
      if (resolver) settle(false);
      if (lastFocus && document.contains(lastFocus)) lastFocus.focus();
    });
    dialog.addEventListener('shown.bs.modal', function () {
      // Cancel is the safe default focus for a destructive prompt.
      dialog.querySelector('[data-fe-cancel]').focus();
    });
    return dialog;
  }

  function settle(value) {
    var r = resolver;
    resolver = null;
    if (r) r(value);
    var inst = window.bootstrap && bootstrap.Modal.getInstance(dialog);
    if (inst) inst.hide();
  }

  /**
   * feConfirm({ title, message, action, danger }) -> Promise<boolean>
   */
  window.feConfirm = function (opts) {
    opts = opts || {};
    var el = build();
    lastFocus = document.activeElement;
    el.querySelector('#feConfirmTitle').textContent = opts.title || 'Are you sure?';
    el.querySelector('#feConfirmBody').textContent = opts.message || '';
    var ok = el.querySelector('[data-fe-ok]');
    ok.textContent = opts.action || 'Delete';
    ok.className = 'btn ' + (opts.danger === false ? 'btn-primary' : 'btn-danger');
    return new Promise(function (resolve) {
      resolver = resolve;
      bootstrap.Modal.getOrCreateInstance(el).show();
    });
  };

  // <form data-confirm="What will be lost" data-confirm-title="Delete X?" data-confirm-action="Delete">
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form.matches || !form.matches('form[data-confirm]')) return;
    if (form.dataset.feConfirmed === '1') { form.dataset.feConfirmed = ''; return; }
    e.preventDefault();
    window.feConfirm({
      title: form.dataset.confirmTitle || 'Delete this item?',
      message: form.dataset.confirm,
      action: form.dataset.confirmAction || 'Delete',
      danger: form.dataset.confirmDanger !== 'false'
    }).then(function (yes) {
      if (!yes) return;
      form.dataset.feConfirmed = '1';
      // Native submit() skips this listener, and keeps CSRF + method fields intact.
      HTMLFormElement.prototype.submit.call(form);
    });
  });


  /* ---------- Section label above each page banner ---------- */
  document.addEventListener('DOMContentLoaded', function () {
    var banner = document.querySelector('.pagetitle');
    var cur = document.querySelector('.fe-nav-link.is-current');
    if (!banner || !cur || banner.querySelector('.fe-eyebrow')) return;
    var h1 = banner.querySelector('h1');
    if (!h1) return;
    var label = document.createElement('span');
    label.className = 'fe-eyebrow';
    var name = cur.querySelector('span') ? cur.querySelector('span').textContent.trim() : '';
    label.textContent = (cur.dataset.group || '') + (name ? ' / ' + name : '');
    h1.parentNode.insertBefore(label, h1);
  });

  /* ---------- Search or jump (Ctrl/Cmd + K) ---------- */
  (function () {
    var pal = document.getElementById('fePalette');
    var input = document.getElementById('fePaletteInput');
    var list = document.getElementById('fePaletteList');
    var items = window.FE_NAV || [];
    if (!pal || !input || !list) return;
    var shown = [], active = 0, lastFocus = null;

    function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    function render() {
      var q = input.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
      shown = items.filter(function (it) {
        var hay = (it.l + ' ' + (it.k || '') + ' ' + it.g).toLowerCase();
        return q.every(function (t) { return hay.indexOf(t) !== -1; });
      });
      if (active >= shown.length) active = 0;
      if (!shown.length) { list.innerHTML = '<li class="fe-palette-empty">Nothing matches that. Try a page name such as Blog or Events.</li>'; return; }
      var html = '', last = '';
      shown.forEach(function (it, i) {
        if (it.g !== last) { html += '<li class="fe-palette-group" role="presentation">' + esc(it.g) + '</li>'; last = it.g; }
        html += '<li role="option" id="fePalOpt' + i + '" class="fe-palette-item' + (it.n ? ' is-new' : '') + (i === active ? ' is-active' : '') + '" data-i="' + i + '" aria-selected="' + (i === active) + '">' +
          '<i class="bi ' + it.i + '" aria-hidden="true"></i><span>' + esc(it.l) + '</span>' + (it.x ? '<small>opens the site</small>' : '') + '</li>';
      });
      list.innerHTML = html;
      input.setAttribute('aria-activedescendant', 'fePalOpt' + active);
      var el = list.querySelector('.is-active'); if (el && el.scrollIntoView) el.scrollIntoView({ block: 'nearest' });
    }

    function open() {
      lastFocus = document.activeElement;
      pal.classList.add('is-open'); input.value = ''; active = 0; render(); input.focus();
    }
    function close() {
      pal.classList.remove('is-open');
      if (lastFocus && document.contains(lastFocus)) lastFocus.focus();
    }
    function go(it) {
      if (!it) return;
      if (it.post) {
        var f = document.createElement('form');
        f.method = 'POST'; f.action = it.u;
        var t = document.createElement('input'); t.type = 'hidden'; t.name = '_token';
        t.value = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        f.appendChild(t); document.body.appendChild(f); f.submit();
      } else if (it.x) { window.open(it.u, '_blank', 'noopener'); close(); }
      else window.location.href = it.u;
    }

    document.addEventListener('keydown', function (e) {
      var typing = /^(input|textarea|select)$/i.test((e.target.tagName || '')) || e.target.isContentEditable;
      if (((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') || (e.key === '/' && !typing && !pal.classList.contains('is-open'))) {
        e.preventDefault(); pal.classList.contains('is-open') ? close() : open(); return;
      }
      if (!pal.classList.contains('is-open')) return;
      if (e.key === 'Escape') { e.preventDefault(); close(); }
      else if (e.key === 'ArrowDown') { e.preventDefault(); active = (active + 1) % Math.max(shown.length, 1); render(); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); active = (active - 1 + shown.length) % Math.max(shown.length, 1); render(); }
      else if (e.key === 'Enter') { e.preventDefault(); go(shown[active]); }
    });
    input.addEventListener('input', function () { active = 0; render(); });
    list.addEventListener('click', function (e) { var li = e.target.closest('[data-i]'); if (li) go(shown[+li.dataset.i]); });
    list.addEventListener('mousemove', function (e) { var li = e.target.closest('[data-i]'); if (li && +li.dataset.i !== active) { active = +li.dataset.i; list.querySelectorAll('.fe-palette-item').forEach(function (n, i) { n.classList.toggle('is-active', +n.dataset.i === active); }); } });
    pal.addEventListener('mousedown', function (e) { if (e.target === pal) close(); });
    document.addEventListener('click', function (e) { if (e.target.closest('#feJump')) open(); });
  })();

  /* ---------- Quick filter on long lists ---------- */
  document.addEventListener('DOMContentLoaded', function () {
    [].forEach.call(document.querySelectorAll('.card-body.p-0 > table.table'), function (table) {
      if (table.hasAttribute('data-no-filter')) return;
      var rows = [].slice.call(table.querySelectorAll('tbody tr')).filter(function (r) { return r.children.length > 1 && !r.querySelector('.fe-empty'); });
      if (rows.length < 6) return;
      var bar = document.createElement('div'); bar.className = 'fe-listbar';
      var input = document.createElement('input'); input.type = 'search'; input.placeholder = 'Filter this list'; input.setAttribute('aria-label', 'Filter this list');
      var count = document.createElement('span'); count.className = 'fe-count';
      bar.appendChild(input); bar.appendChild(count);
      table.parentNode.insertBefore(bar, table);
      var none = document.createElement('tr'); none.hidden = true;
      none.innerHTML = '<td colspan="' + (table.querySelector('thead tr') ? table.querySelector('thead tr').children.length : 1) + '" class="py-4 text-muted">Nothing in this list matches.</td>';
      table.querySelector('tbody').appendChild(none);
      function apply() {
        var q = input.value.trim().toLowerCase(), n = 0;
        rows.forEach(function (r) { var show = !q || r.textContent.toLowerCase().indexOf(q) !== -1; r.hidden = !show; if (show) n++; });
        none.hidden = n !== 0; count.textContent = (q ? n + ' of ' : '') + rows.length + (rows.length === 1 ? ' item' : ' items');
      }
      input.addEventListener('input', apply); apply();
    });
  });

  /* ---------- Unsaved changes ---------- */
  var dirty = new WeakSet();
  var dirtyCount = 0;

  function markDirty(form) {
    if (!dirty.has(form)) { dirty.add(form); dirtyCount++; }
  }

  document.addEventListener('input', function (e) {
    var form = e.target.closest && e.target.closest('form[data-unsaved]');
    if (form) markDirty(form);
  });
  document.addEventListener('change', function (e) {
    var form = e.target.closest && e.target.closest('form[data-unsaved]');
    if (form) markDirty(form);
  });
  document.addEventListener('submit', function (e) {
    if (e.target.matches && e.target.matches('form[data-unsaved]') && dirty.has(e.target)) {
      dirty.delete(e.target); dirtyCount--;
    }
  }, true);
  document.addEventListener('reset', function (e) {
    if (e.target.matches && e.target.matches('form[data-unsaved]') && dirty.has(e.target)) {
      dirty.delete(e.target); dirtyCount--;
    }
  });
  window.addEventListener('beforeunload', function (e) {
    if (dirtyCount > 0) { e.preventDefault(); e.returnValue = ''; }
  });
})();
