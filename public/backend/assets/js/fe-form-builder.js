/* Form builder: edits a list of fields, writes it to #fieldsJson, and renders a live preview. */
(function () {
  'use strict';
  var cfg = window.FE_FORM;
  var form = document.getElementById('formBuilder');
  if (!cfg || !form) return;

  var json = document.getElementById('fieldsJson');
  var list = document.getElementById('fbFields');
  var preview = document.getElementById('fbPreview');
  var fields = (cfg.fields || []).map(function (f) { return Object.assign({ options: [], maps_to: '', placeholder: '', help: '' }, f); });
  var openKey = null;

  function uid() { return 'f_' + Math.random().toString(36).slice(2, 8); }
  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }
  function isChoice(t) { return cfg.choice.indexOf(t) !== -1; }
  function find(key) { return fields.findIndex(function (f) { return f.key === key; }); }

  function sync() {
    json.value = JSON.stringify(fields);
    renderPreview();
  }

  /* ---------- preview ---------- */
  function renderPreview() {
    var html = '';
    fields.forEach(function (f) {
      var label = '<label class="fp-label">' + esc(f.label) + (f.required ? ' <span aria-hidden="true">*</span>' : '') + '</label>';
      var input = '';
      if (f.type === 'slot') input = '<div class="fp-choices" style="flex-direction:row;flex-wrap:wrap;gap:6px">' + ['Tue 3 Nov', 'Wed 4 Nov', 'Thu 5 Nov'].map(function (d, i) { return '<span style="padding:6px 10px;border:1px solid #c9d1d1;border-radius:8px;font-size:12px;font-weight:600;' + (i === 0 ? 'background:#153f5d;color:#fff' : '') + '">' + d + '</span>'; }).join('') + '</div><div class="fp-help">Visitors pick from the open times in Availability &amp; calendar.</div>';
      else if (f.type === 'textarea') input = '<textarea class="fp-input" rows="3" disabled placeholder="' + esc(f.placeholder) + '"></textarea>';
      else if (f.type === 'select') input = '<select class="fp-input" disabled><option>Choose…</option>' + f.options.map(function (o) { return '<option>' + esc(o) + '</option>'; }).join('') + '</select>';
      else if (f.type === 'radio' || f.type === 'checkbox') {
        input = '<div class="fp-choices">' + (f.options.length ? f.options : ['Option 1']).map(function (o) {
          return '<label class="fp-choice"><input type="' + f.type + '" disabled> ' + esc(o) + '</label>';
        }).join('') + '</div>';
      } else input = '<input class="fp-input" type="' + (f.type === 'number' || f.type === 'date' ? f.type : 'text') + '" disabled placeholder="' + esc(f.placeholder) + '">';
      html += '<div class="fp-field">' + label + input + (f.help ? '<div class="fp-help">' + esc(f.help) + '</div>' : '') + '</div>';
    });
    var consent = document.getElementById('fb-consent');
    if (consent && consent.checked) {
      html += '<label class="fp-choice fp-consent"><input type="checkbox" disabled> ' + esc((document.getElementById('fb-consent-text') || {}).value || '') + '</label>';
    }
    var btn = (document.getElementById('fb-submit') || {}).value || cfg.submitLabel || 'Send';
    if (!fields.length && cfg.locked) html = '<p class="fp-help">This form\'s layout comes from the website.</p>';
    else if (!fields.length) html = '<p class="fp-help">Add a field to start.</p>';
    preview.innerHTML = html + '<button type="button" class="fp-btn" disabled>' + esc(btn) + '</button>';
  }

  /* ---------- field list ---------- */
  function render() {
    if (!list) { sync(); return; }
    list.innerHTML = '';
    fields.forEach(function (f, i) {
      var open = openKey === f.key;
      var row = document.createElement('div');
      row.className = 'fb-field' + (open ? ' is-open' : '');
      row.dataset.key = f.key;
      row.innerHTML =
        '<div class="fb-field-head">' +
          '<button type="button" class="fb-field-toggle" data-act="toggle" aria-expanded="' + open + '">' +
            '<span class="fb-field-label">' + esc(f.label) + (f.required ? ' <span class="fb-req" title="Required">*</span>' : '') + '</span>' +
            '<span class="fb-field-type">' + esc(cfg.types[f.type] || f.type) + '</span>' +
          '</button>' +
          '<div class="fb-field-tools">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary" data-act="up" aria-label="Move up"' + (i === 0 ? ' disabled' : '') + '><i class="bi bi-arrow-up" aria-hidden="true"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary" data-act="down" aria-label="Move down"' + (i === fields.length - 1 ? ' disabled' : '') + '><i class="bi bi-arrow-down" aria-hidden="true"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary" data-act="dup" aria-label="Duplicate field"><i class="bi bi-files" aria-hidden="true"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger" data-act="del" aria-label="Delete field"><i class="bi bi-trash" aria-hidden="true"></i></button>' +
          '</div>' +
        '</div>' +
        (open ? editor(f) : '');
      list.appendChild(row);
    });
    sync();
  }

  function editor(f) {
    var h = '<div class="fb-field-body">';
    h += '<div class="row g-3">';
    h += '<div class="col-md-7"><label class="form-label">Question</label><input class="form-control" data-prop="label" value="' + esc(f.label) + '" maxlength="120"></div>';
    h += '<div class="col-md-5"><label class="form-label">Type</label><select class="form-select" data-prop="type">' +
         Object.keys(cfg.types).map(function (t) { return '<option value="' + t + '"' + (t === f.type ? ' selected' : '') + '>' + esc(cfg.types[t]) + '</option>'; }).join('') + '</select></div>';
    if (isChoice(f.type)) {
      h += '<div class="col-12"><label class="form-label">Choices (one per line)</label><textarea class="form-control" rows="4" data-prop="options">' + esc((f.options || []).join('\n')) + '</textarea></div>';
    } else if (f.type !== 'date' && f.type !== 'number' && f.type !== 'slot') {
      h += '<div class="col-12"><label class="form-label">Placeholder (optional)</label><input class="form-control" data-prop="placeholder" value="' + esc(f.placeholder) + '" maxlength="160"></div>';
    }
    h += '<div class="col-12"><label class="form-label">Help text (optional)</label><input class="form-control" data-prop="help" value="' + esc(f.help) + '" maxlength="240"></div>';
    if (f.type !== 'email' && f.type !== 'slot') {
      h += '<div class="col-md-7"><label class="form-label">Brevo column</label><select class="form-select" data-prop="maps_to">' +
           Object.keys(cfg.maps).map(function (m) { return '<option value="' + m + '"' + (m === (f.maps_to || '') ? ' selected' : '') + '>' + esc(cfg.maps[m]) + '</option>'; }).join('') + '</select></div>';
    }
    h += '<div class="col-md-5 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="req-' + f.key + '" data-prop="required"' + (f.required ? ' checked' : '') + '><label class="form-check-label" for="req-' + f.key + '">Required</label></div></div>';
    h += '</div></div>';
    return h;
  }

  /* ---------- events ---------- */
  if (list) {
    list.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-act]');
      if (!btn) return;
      var key = btn.closest('.fb-field').dataset.key;
      var i = find(key);
      var act = btn.dataset.act;
      if (act === 'toggle') { openKey = openKey === key ? null : key; render(); return; }
      if (act === 'up' && i > 0) { fields.splice(i - 1, 0, fields.splice(i, 1)[0]); render(); focusAct(key, 'up', 'down'); }
      if (act === 'down' && i < fields.length - 1) { fields.splice(i + 1, 0, fields.splice(i, 1)[0]); render(); focusAct(key, 'down', 'up'); }
      if (act === 'dup') {
        var copy = JSON.parse(JSON.stringify(fields[i])); copy.key = uid(); copy.label += ' (copy)';
        fields.splice(i + 1, 0, copy); openKey = copy.key; render();
      }
      if (act === 'del') {
        window.feConfirm({
          title: 'Delete this field?',
          message: '“' + fields[i].label + '” will be removed from the form. Answers already received keep their data.',
          action: 'Delete field'
        }).then(function (yes) { if (yes) { fields.splice(find(key), 1); if (openKey === key) openKey = null; render(); } });
      }
    });

    function focusAct(key, act, fallback) {
      var row = list.querySelector('[data-key="' + key + '"]');
      var b = row && (row.querySelector('[data-act="' + act + '"]:not([disabled])') || row.querySelector('[data-act="' + fallback + '"]'));
      if (b) b.focus();
    }

    list.addEventListener('input', function (e) { change(e.target, false); });
    list.addEventListener('change', function (e) { change(e.target, true); });

    function change(el, commit) {
      var prop = el.dataset && el.dataset.prop;
      if (!prop) return;
      var key = el.closest('.fb-field').dataset.key;
      var f = fields[find(key)];
      if (!f) return;
      if (prop === 'required') f.required = el.checked;
      else if (prop === 'options') f.options = el.value.split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
      else f[prop] = el.value;
      if (prop === 'type' && commit) { if (f.type === 'email') f.maps_to = ''; render(); return; }
      if (prop === 'label') {
        var span = el.closest('.fb-field').querySelector('.fb-field-label');
        if (span) span.firstChild.textContent = f.label;
      }
      sync();
    }

    document.querySelectorAll('[data-add]').forEach(function (b) {
      b.addEventListener('click', function () {
        var type = b.dataset.add;
        var f = { key: uid(), label: cfg.types[type], type: type, required: false, placeholder: '', help: '', options: isChoice(type) ? ['Option 1', 'Option 2'] : [], maps_to: '' };
        if (type === 'email') { f.label = 'Email'; f.required = true; }
        fields.push(f); openKey = f.key; render();
        var row = list.querySelector('[data-key="' + f.key + '"] [data-prop="label"]');
        if (row) { row.focus(); row.select(); }
      });
    });
  }

  ['fb-consent', 'fb-consent-text', 'fb-submit'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) { el.addEventListener('input', renderPreview); el.addEventListener('change', renderPreview); }
  });

  form.addEventListener('submit', function () { json.value = JSON.stringify(fields); });
  render();
})();
