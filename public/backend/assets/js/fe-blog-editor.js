/*
 * Blog editor: one screen to write a post as a list of blocks.
 * State lives in `blocks`; structural changes re-render, typing never does (so the caret stays put).
 */
(function () {
  'use strict';

  var cfg = window.FE_BLOG;
  var root = document.getElementById('blogEditor');
  if (!cfg || !root) return;

  var $ = function (id) { return document.getElementById(id); };
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var list = $('beBlocks');

  var TYPES = {
    paragraph: { label: 'Text', icon: 'bi-text-paragraph' },
    heading:   { label: 'Heading', icon: 'bi-type-h2' },
    image:     { label: 'Image', icon: 'bi-image' },
    list:      { label: 'List', icon: 'bi-list-ul' },
    quote:     { label: 'Quote', icon: 'bi-quote' },
    video:     { label: 'Video', icon: 'bi-play-btn' },
    button:    { label: 'Button', icon: 'bi-cursor' },
    form:      { label: 'Signup form', icon: 'bi-envelope-paper' }
  };

  var seq = 0;
  var blocks = (cfg.blocks || []).map(function (b) { return prep(b); });
  var status = cfg.status;
  var dirty = false, saving = false, timer = null, slugTouched = !cfg.titleIsPlaceholder, dragCid = null;

  /* ---------- helpers ---------- */
  function prep(b) {
    b.cid = 'c' + (++seq);
    b.extras = (b.extras && !Array.isArray(b.extras)) ? b.extras : {};
    if (b.type === 'list' && !Array.isArray(b.content)) b.content = b.content ? String(b.content).split(/\r?\n/) : [];
    if (b.type === 'paragraph' && b.content == null) b.content = '';
    return b;
  }
  function h(tag, attrs, kids) {
    var el = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (k) {
      if (k === 'class') el.className = attrs[k];
      else if (k === 'text') el.textContent = attrs[k];
      else if (k === 'html') el.innerHTML = attrs[k];
      else if (attrs[k] === true) el.setAttribute(k, '');
      else if (attrs[k] !== false && attrs[k] != null) el.setAttribute(k, attrs[k]);
    });
    (kids || []).forEach(function (c) { if (c) el.appendChild(c); });
    return el;
  }
  function find(cid) { return blocks.findIndex(function (b) { return b.cid === cid; }); }
  function slugify(s) { return String(s).toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 80); }
  function announce(msg) { var l = $('beLive'); if (!l) { l = h('div', { id: 'beLive', class: 'visually-hidden', role: 'status', 'aria-live': 'polite' }); root.appendChild(l); } l.textContent = msg; }

  function isBlank(b) {
    switch (b.type) {
      case 'paragraph': return !String(b.content || '').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
      case 'heading': case 'quote': return !String(b.content || '').trim();
      case 'list': return !(b.content || []).some(function (i) { return String(i).trim(); });
      case 'image': return !b.content;
      case 'video': return b.extras.source === 'upload' ? false : !/^https?:\/\//i.test(String(b.content || '').trim());
      case 'button': return !String(b.content || '').trim() || !String(b.extras.url || '').trim();
      case 'form': return !b.extras.form_id;
    }
    return true;
  }

  /* ---------- state flags ---------- */
  function markDirty() {
    dirty = true;
    setSaved('Unsaved changes');
    if (status !== 'published') {          // live posts are only saved with Update
      clearTimeout(timer);
      timer = setTimeout(function () { save({ autosave: true }); }, 1200);
    }
  }
  function setSaved(text, isError) {
    var el = $('beSaved'); el.textContent = text; el.classList.toggle('is-error', !!isError);
  }
  function setBadge() {
    var b = $('beBadge');
    b.textContent = status === 'published' ? 'Published' : status === 'scheduled' ? 'Scheduled' : 'Draft';
    b.className = 'badge ' + (status === 'published' ? 'bg-success' : status === 'scheduled' ? 'bg-primary' : 'bg-warning');
  }

  /* ---------- block menu ---------- */
  var menu = null;
  function openMenu(anchor, index) {
    closeMenu();
    menu = h('div', { class: 'be-menu', role: 'menu' });
    Object.keys(TYPES).forEach(function (t) {
      var btn = h('button', { type: 'button', class: 'be-menu-item', role: 'menuitem' }, [
        h('i', { class: 'bi ' + TYPES[t].icon, 'aria-hidden': 'true' }), h('span', { text: TYPES[t].label })
      ]);
      btn.addEventListener('click', function () { closeMenu(); insertBlock(t, index); });
      menu.appendChild(btn);
    });
    root.appendChild(menu);
    var r = anchor.getBoundingClientRect(), rr = root.getBoundingClientRect();
    menu.style.top = (r.bottom - rr.top + 6) + 'px';
    menu.style.left = Math.max(8, Math.min(r.left - rr.left, rr.width - menu.offsetWidth - 8)) + 'px';
    menu.querySelector('button').focus();
    menu.addEventListener('keydown', function (e) {
      var items = [].slice.call(menu.querySelectorAll('button')), i = items.indexOf(document.activeElement);
      if (e.key === 'ArrowDown' || e.key === 'ArrowRight') { e.preventDefault(); items[(i + 1) % items.length].focus(); }
      if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') { e.preventDefault(); items[(i - 1 + items.length) % items.length].focus(); }
    });
    menu._anchor = anchor;
  }
  function closeMenu() { if (menu) { var a = menu._anchor; menu.remove(); menu = null; if (a && document.contains(a)) a.focus(); } }
  document.addEventListener('click', function (e) { if (menu && !menu.contains(e.target) && !e.target.closest('.be-plus, #beAddEnd')) { menu.remove(); menu = null; } });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && menu) closeMenu(); });

  function insertBlock(type, index, content) {
    var b = prep({ id: null, type: type, content: content != null ? content : (type === 'list' ? [] : ''), extras: {} });
    if (type === 'form' && cfg.forms.length) b.extras.form_id = cfg.forms[0].id;
    if (type === 'video') b.extras.orientation = 'landscape';
    blocks.splice(index, 0, b);
    render(b.cid);
    markDirty();
  }

  /* ---------- render ---------- */
  function render(focusCid, focusEnd) {
    list.innerHTML = '';
    if (!blocks.length) {
      list.appendChild(h('p', { class: 'be-empty', text: 'Start writing, or add a block below.' }));
    }
    blocks.forEach(function (b, i) {
      list.appendChild(inserter(i));
      list.appendChild(blockEl(b, i));
    });
    if (focusCid) {
      var target = list.querySelector('[data-cid="' + focusCid + '"] [data-focus]');
      if (target) { target.focus(); if (focusEnd) placeCaretEnd(target); }
    }
  }
  function inserter(i) {
    var b = h('button', { type: 'button', class: 'be-plus', 'aria-label': 'Add a block here', 'aria-haspopup': 'true' }, [h('i', { class: 'bi bi-plus-lg', 'aria-hidden': 'true' })]);
    b.addEventListener('click', function () { openMenu(b, i); });
    return h('div', { class: 'be-ins' }, [b]);
  }
  function placeCaretEnd(el) {
    if (el.isContentEditable) {
      var r = document.createRange(); r.selectNodeContents(el); r.collapse(false);
      var s = window.getSelection(); s.removeAllRanges(); s.addRange(r);
    } else if (el.setSelectionRange) { var n = el.value.length; el.setSelectionRange(n, n); }
  }

  function blockEl(b, i) {
    var el = h('div', { class: 'be-block t-' + b.type, 'data-cid': b.cid, 'data-type': b.type });
    var grip = h('button', { type: 'button', class: 'be-grip', 'aria-label': 'Drag to move this block', title: 'Drag to move' }, [h('i', { class: 'bi bi-grip-vertical', 'aria-hidden': 'true' })]);
    grip.addEventListener('mousedown', function () { el.draggable = true; });
    grip.addEventListener('touchstart', function () { el.draggable = true; }, { passive: true });
    el.addEventListener('dragstart', function (e) { dragCid = b.cid; el.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; try { e.dataTransfer.setData('text/plain', b.cid); } catch (x) {} });
    el.addEventListener('dragend', function () { el.draggable = false; el.classList.remove('is-dragging'); clearDrop(); dragCid = null; });

    var body = h('div', { class: 'be-body' }, [bodyFor(b)]);
    var tools = h('div', { class: 'be-tools' }, [
      tool('bi-arrow-up', 'Move up', function () { move(b.cid, -1); }, i === 0),
      tool('bi-arrow-down', 'Move down', function () { move(b.cid, 1); }, i === blocks.length - 1),
      tool('bi-trash', 'Delete block', function () { remove(b.cid); }, false, true)
    ]);
    el.appendChild(h('div', { class: 'be-gutter' }, [grip]));
    el.appendChild(body);
    el.appendChild(tools);
    return el;
  }
  function tool(icon, label, fn, disabled, danger) {
    var b = h('button', { type: 'button', class: 'be-tool' + (danger ? ' is-danger' : ''), 'aria-label': label, title: label, disabled: disabled }, [h('i', { class: 'bi ' + icon, 'aria-hidden': 'true' })]);
    b.addEventListener('click', fn);
    return b;
  }

  function bodyFor(b) {
    switch (b.type) {
      case 'paragraph': return paragraph(b);
      case 'heading': return heading(b);
      case 'quote': return quote(b);
      case 'list': return listBlock(b);
      case 'image': return image(b);
      case 'video': return video(b);
      case 'button': return button(b);
      case 'form': return formBlock(b);
    }
    return h('div');
  }

  function field(label, input) { return h('label', { class: 'be-field' }, [h('span', { class: 'be-field-label', text: label }), input]); }
  function bind(input, b, setter) { input.addEventListener('input', function () { setter(input.value); markDirty(); }); return input; }
  function grow(t) { t.style.height = 'auto'; t.style.height = (t.scrollHeight + 2) + 'px'; }

  /* paragraph: rich text with bold, italic and links */
  function paragraph(b) {
    var ed = h('div', { class: 'be-text', contenteditable: 'true', role: 'textbox', 'aria-multiline': 'true', 'aria-label': 'Text', 'data-ph': 'Write something, or press + to add a block', 'data-focus': '1', spellcheck: 'true' });
    ed.innerHTML = b.content || '';
    ed.addEventListener('input', function () { b.content = ed.innerHTML; markDirty(); });
    ed.addEventListener('paste', function (e) {
      e.preventDefault();
      var t = (e.clipboardData || window.clipboardData).getData('text/plain');
      document.execCommand('insertText', false, t);
    });
    ed.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); split(b, ed); return; }
      if (e.key === 'Backspace' && !ed.textContent.trim() && blocks.length > 0) { e.preventDefault(); removeSilently(b.cid); return; }
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openLink(ed, fmt); }
    });

    var fmt = h('div', { class: 'be-fmt', role: 'toolbar', 'aria-label': 'Text formatting' });
    [['bold', 'bi-type-bold', 'Bold (Ctrl or Cmd + B)'], ['italic', 'bi-type-italic', 'Italic (Ctrl or Cmd + I)']].forEach(function (c) {
      var btn = h('button', { type: 'button', class: 'be-fmt-btn', 'aria-label': c[2], title: c[2] }, [h('i', { class: 'bi ' + c[1], 'aria-hidden': 'true' })]);
      btn.addEventListener('mousedown', function (e) { e.preventDefault(); });
      btn.addEventListener('click', function () { ed.focus(); document.execCommand(c[0]); b.content = ed.innerHTML; markDirty(); });
      fmt.appendChild(btn);
    });
    var linkBtn = h('button', { type: 'button', class: 'be-fmt-btn', 'aria-label': 'Add link (Ctrl or Cmd + K)', title: 'Add link' }, [h('i', { class: 'bi bi-link-45deg', 'aria-hidden': 'true' })]);
    linkBtn.addEventListener('mousedown', function (e) { e.preventDefault(); });
    linkBtn.addEventListener('click', function () { openLink(ed, fmt); });
    fmt.appendChild(linkBtn);
    return h('div', { class: 'be-rich' }, [fmt, ed]);
  }

  function openLink(ed, fmt) {
    var sel = window.getSelection();
    if (!sel.rangeCount || sel.isCollapsed || !ed.contains(sel.anchorNode)) { announce('Select some text first, then add the link.'); setSaved('Select some text first', true); return; }
    var saved = sel.getRangeAt(0).cloneRange();
    var existing = fmt.querySelector('.be-link'); if (existing) existing.remove();
    var input = h('input', { type: 'url', class: 'form-control form-control-sm', placeholder: 'https://', 'aria-label': 'Link address' });
    var ok = h('button', { type: 'button', class: 'btn btn-sm btn-primary', text: 'Apply' });
    var un = h('button', { type: 'button', class: 'btn btn-sm btn-outline-secondary', text: 'Remove link' });
    var box = h('div', { class: 'be-link' }, [input, ok, un]);
    fmt.appendChild(box); input.focus();
    function restore() { ed.focus(); var s = window.getSelection(); s.removeAllRanges(); s.addRange(saved); }
    function done() { box.remove(); var b = blocks[find(ed.closest('.be-block').dataset.cid)]; if (b) { b.content = ed.innerHTML; markDirty(); } }
    ok.addEventListener('click', function () { var u = input.value.trim(); if (!u) return; restore(); document.execCommand('createLink', false, /^[a-z]+:|^\//i.test(u) ? u : 'https://' + u); done(); });
    un.addEventListener('click', function () { restore(); document.execCommand('unlink'); done(); });
    input.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); ok.click(); } if (e.key === 'Escape') { box.remove(); restore(); } });
  }

  function split(b, ed) {
    var sel = window.getSelection(), tail = '';
    if (sel.rangeCount && ed.contains(sel.anchorNode)) {
      var r = document.createRange();
      r.setStart(sel.getRangeAt(0).startContainer, sel.getRangeAt(0).startOffset);
      r.setEnd(ed, ed.childNodes.length);
      var d = document.createElement('div'); d.appendChild(r.extractContents()); tail = d.innerHTML;
      b.content = ed.innerHTML;
    }
    var nb = prep({ id: null, type: 'paragraph', content: tail, extras: {} });
    blocks.splice(find(b.cid) + 1, 0, nb);
    render(nb.cid);
    var t = list.querySelector('[data-cid="' + nb.cid + '"] .be-text');
    if (t) { var rg = document.createRange(); rg.setStart(t, 0); rg.collapse(true); var s2 = window.getSelection(); s2.removeAllRanges(); s2.addRange(rg); }
    markDirty();
  }

  function heading(b) {
    var inp = h('input', { class: 'be-heading', value: b.content || '', placeholder: 'Heading', 'aria-label': 'Heading', maxlength: '200', 'data-focus': '1' });
    bind(inp, b, function (v) { b.content = v; });
    inp.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); insertBlock('paragraph', find(b.cid) + 1); }
      if (e.key === 'Backspace' && !inp.value) { e.preventDefault(); removeSilently(b.cid); }
    });
    return inp;
  }

  function quote(b) {
    var t = h('textarea', { class: 'be-quote', rows: '2', placeholder: 'Quote', 'aria-label': 'Quote', 'data-focus': '1' }); t.value = b.content || '';
    t.addEventListener('input', function () { b.content = t.value; grow(t); markDirty(); }); setTimeout(function () { grow(t); }, 0);
    var a = h('input', { class: 'form-control form-control-sm', value: b.extras.author || '', placeholder: 'Who said it (optional)', 'aria-label': 'Who said it', maxlength: '120' });
    bind(a, b, function (v) { b.extras.author = v; });
    return h('div', {}, [t, a]);
  }

  function listBlock(b) {
    var t = h('textarea', { class: 'be-list', rows: '3', placeholder: 'One item per line', 'aria-label': 'List items', 'data-focus': '1' }); t.value = (b.content || []).join('\n');
    t.addEventListener('input', function () { b.content = t.value.split(/\r?\n/); grow(t); markDirty(); }); setTimeout(function () { grow(t); }, 0);
    return h('div', {}, [t, h('p', { class: 'be-hint', text: 'Each line becomes a bullet point.' })]);
  }

  function image(b) {
    var wrap = h('div', { class: 'be-image' });
    var file = h('input', { type: 'file', accept: 'image/jpeg,image/png,image/webp,image/gif', hidden: true });
    var pick = h('button', { type: 'button', class: 'be-drop', 'data-focus': '1' }, [h('i', { class: 'bi bi-image', 'aria-hidden': 'true' }), h('span', { text: b.url ? 'Replace image' : 'Choose an image' })]);
    pick.addEventListener('click', function () { file.click(); });
    file.addEventListener('change', function () { if (file.files[0]) upload(file.files[0], b, pick); });
    if (b.url) wrap.appendChild(h('img', { src: b.url, alt: b.extras.alt || '' }));
    wrap.appendChild(pick); wrap.appendChild(file);
    if (b.content) {
      wrap.appendChild(bind(h('input', { class: 'form-control form-control-sm', value: b.extras.caption || '', placeholder: 'Caption (optional)', 'aria-label': 'Caption', maxlength: '200' }), b, function (v) { b.extras.caption = v; }));
      wrap.appendChild(bind(h('input', { class: 'form-control form-control-sm', value: b.extras.alt || '', placeholder: 'Describe the image for screen readers', 'aria-label': 'Image description', maxlength: '200' }), b, function (v) { b.extras.alt = v; }));
    }
    return wrap;
  }
  function upload(f, b, btn) {
    var fd = new FormData(); fd.append('image', f);
    btn.disabled = true; btn.lastChild.textContent = 'Uploading…';
    fetch(cfg.urls.upload, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json().then(function (j) { if (!r.ok) throw j; return j; }); })
      .then(function (j) { b.content = j.path; b.url = j.url; render(b.cid); markDirty(); })
      .catch(function (e) { btn.disabled = false; btn.lastChild.textContent = 'Choose an image'; setSaved((e && e.message) || 'That image could not be uploaded (JPG, PNG, WebP or GIF, up to 5 MB).', true); });
  }

  function video(b) {
    var local = b.extras.source === 'upload';
    var u = h('input', { class: 'form-control', value: local ? 'Uploaded video (kept as it is)' : (b.content || ''), placeholder: 'Paste a YouTube or Vimeo link', 'aria-label': 'Video link', disabled: local, 'data-focus': '1' });
    if (!local) bind(u, b, function (v) { b.content = v; });
    var o = h('select', { class: 'form-select form-select-sm', 'aria-label': 'Shape' }, [h('option', { value: 'landscape', text: 'Landscape' }), h('option', { value: 'portrait', text: 'Portrait' })]);
    o.value = b.extras.orientation === 'portrait' ? 'portrait' : 'landscape';
    o.addEventListener('change', function () { b.extras.orientation = o.value; markDirty(); });
    return h('div', { class: 'be-video' }, [u, o]);
  }

  function button(b) {
    var l = bind(h('input', { class: 'form-control', value: b.content || '', placeholder: 'Button text', 'aria-label': 'Button text', maxlength: '80', 'data-focus': '1' }), b, function (v) { b.content = v; });
    var u = bind(h('input', { class: 'form-control', value: b.extras.url || '', placeholder: 'https://', 'aria-label': 'Button link' }), b, function (v) { b.extras.url = v; });
    return h('div', { class: 'be-button' }, [l, u]);
  }

  function formBlock(b) {
    var s = h('select', { class: 'form-select', 'aria-label': 'Form', 'data-focus': '1' }, cfg.forms.map(function (f) { return h('option', { value: f.id, text: f.name }); }));
    s.value = b.extras.form_id || (cfg.forms[0] && cfg.forms[0].id) || '';
    if (!b.extras.form_id && s.value) b.extras.form_id = parseInt(s.value, 10);
    s.addEventListener('change', function () { b.extras.form_id = parseInt(s.value, 10); markDirty(); });
    var hd = bind(h('input', { class: 'form-control', value: b.extras.heading || '', placeholder: 'Heading above the form (optional)', 'aria-label': 'Heading', maxlength: '160' }), b, function (v) { b.extras.heading = v; });
    return h('div', { class: 'be-formblock' }, [h('p', { class: 'be-hint', text: 'Readers see this form here in the post.' }), s, hd]);
  }

  /* ---------- structure ---------- */
  function move(cid, dir) {
    var i = find(cid), j = i + dir;
    if (i < 0 || j < 0 || j >= blocks.length) return;
    blocks.splice(j, 0, blocks.splice(i, 1)[0]);
    render();
    var btn = list.querySelector('[data-cid="' + cid + '"] [aria-label="' + (dir < 0 ? 'Move up' : 'Move down') + '"]:not([disabled])') ||
              list.querySelector('[data-cid="' + cid + '"] [aria-label="' + (dir < 0 ? 'Move down' : 'Move up') + '"]');
    if (btn) btn.focus();
    announce((TYPES[blocks[j].type] || {}).label + ' moved to position ' + (j + 1) + ' of ' + blocks.length);
    markDirty();
  }
  function remove(cid) {
    var b = blocks[find(cid)]; if (!b) return;
    if (isBlank(b)) { removeSilently(cid); return; }
    window.feConfirm({ title: 'Delete this block?', message: 'This ' + (TYPES[b.type].label.toLowerCase()) + ' block will be removed from the post.', action: 'Delete block' })
      .then(function (yes) { if (yes) removeSilently(cid); });
  }
  function removeSilently(cid) {
    var i = find(cid); if (i < 0) return;
    blocks.splice(i, 1);
    var prev = blocks[i - 1] || blocks[i];
    render(prev && prev.cid, true);
    markDirty();
  }

  /* drag and drop */
  function dropIndex(y) {
    var els = [].slice.call(list.querySelectorAll('.be-block'));
    for (var i = 0; i < els.length; i++) { var r = els[i].getBoundingClientRect(); if (y < r.top + r.height / 2) return i; }
    return els.length;
  }
  function clearDrop() { var d = list.querySelector('.be-dropline'); if (d) d.remove(); }
  list.addEventListener('dragover', function (e) {
    if (!dragCid) return; e.preventDefault();
    clearDrop();
    var els = [].slice.call(list.querySelectorAll('.be-block')), i = dropIndex(e.clientY), line = h('div', { class: 'be-dropline' });
    if (i >= els.length) els[els.length - 1].after(line); else els[i].before(line);
  });
  list.addEventListener('drop', function (e) {
    if (!dragCid) return; e.preventDefault();
    var to = dropIndex(e.clientY), from = find(dragCid); clearDrop();
    if (from < 0) return;
    var b = blocks.splice(from, 1)[0]; if (from < to) to--;
    blocks.splice(to, 0, b); dragCid = null; render(); markDirty();
    announce((TYPES[b.type] || {}).label + ' moved to position ' + (to + 1) + ' of ' + blocks.length);
  });

  $('beAddEnd').addEventListener('click', function () { openMenu($('beAddEnd'), blocks.length); });

  /* ---------- settings ---------- */
  ['beCategory', 'beExcerpt', 'beMetaTitle', 'beMetaDesc', 'beSignupHeading', 'beSignupForm', 'beSignupAfter'].forEach(function (id) {
    var el = $(id); if (el) { el.addEventListener('input', markDirty); el.addEventListener('change', markDirty); }
  });
  [].forEach.call(document.getElementsByName('beSignupPos'), function (r) { r.addEventListener('change', markDirty); });
  function growTitle() { var t = $('beTitle'); t.style.height = 'auto'; t.style.height = t.scrollHeight + 'px'; }
  $('beTitle').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); var f = list.querySelector('[data-focus]'); if (f) f.focus(); } });
  $('beTitle').addEventListener('input', function () {
    growTitle();
    if (!slugTouched && status === 'draft') $('beSlug').value = slugify($('beTitle').value);
    markDirty();
  });
  $('beSlug').addEventListener('input', function () { slugTouched = true; markDirty(); });
  $('beSlug').addEventListener('blur', function () { this.value = slugify(this.value) || this.value; });

  $('beCoverFile').addEventListener('change', function () {
    var f = this.files[0]; if (!f) return;
    var fd = new FormData(); fd.append('image', f);
    $('beCoverLabel').textContent = 'Uploading…';
    fetch(cfg.urls.cover, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json().then(function (j) { if (!r.ok) throw j; return j; }); })
      .then(function (j) { $('beCover').innerHTML = ''; $('beCover').appendChild(h('img', { src: j.url, alt: 'Cover image' })); $('beCoverLabel').textContent = 'Replace image'; })
      .catch(function () { $('beCoverLabel').textContent = 'Upload image'; setSaved('That image could not be uploaded (JPG, PNG or WebP, up to 5 MB).', true); });
  });

  /* ---------- save and publish ---------- */
  function payload(extra) {
    var pos = (document.querySelector('input[name="beSignupPos"]:checked') || {}).value || 'end';
    var p = {
      title: $('beTitle').value.trim() || 'Untitled post',
      slug: $('beSlug').value.trim(),
      category: $('beCategory').value.trim(),
      excerpt: $('beExcerpt').value.trim(),
      meta_title: $('beMetaTitle').value.trim(),
      meta_description: $('beMetaDesc').value.trim(),
      signup_form_id: $('beSignupForm').value ? parseInt($('beSignupForm').value, 10) : null,
      signup_position: pos,
      signup_after: pos === 'after_paragraph' ? parseInt($('beSignupAfter').value || '3', 10) : null,
      signup_heading: $('beSignupHeading').value.trim(),
      blocks: blocks.map(function (b) { return { id: b.id || null, type: b.type, content: b.content, extras: b.extras }; })
    };
    return Object.assign(p, extra || {});
  }

  function save(opts) {
    opts = opts || {};
    if (saving) { clearTimeout(timer); timer = setTimeout(function () { save(opts); }, 600); return Promise.resolve(false); }
    clearTimeout(timer);
    saving = true; setSaved('Saving…');
    var body = payload({ autosave: !!opts.autosave, publish: opts.publish || 'keep', published_at: opts.published_at || null });
    return fetch(cfg.urls.save, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify(body), credentials: 'same-origin'
    }).then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, j: j }; }); })
      .then(function (res) {
        saving = false;
        if (!res.ok) {
          setSaved(res.j.message || 'Could not save. Your changes are still here.', true);
          if (opts.publish) announce(res.j.message || 'Could not save');
          return false;
        }
        var j = res.j;
        // hand the server's ids back to the blocks it kept, in order
        var kept = j.blocks.slice(), ki = 0;
        blocks.forEach(function (b) { if (!isBlank(b) && kept[ki]) { b.id = kept[ki].id; ki++; } });
        status = j.blog_status; cfg.publishedAt = j.published_at; cfg.publicUrl = j.public_url;
        if ($('beSlug').value !== j.slug) $('beSlug').value = j.slug;
        dirty = false; setSaved('Saved ' + j.saved_at); setBadge(); renderPublish();
        return true;
      })
      .catch(function () { saving = false; setSaved('Network problem. Your changes are still here.', true); return false; });
  }

  function preview() {
    // open the tab inside the click so the browser does not block it, then save and point it at the post
    var w = window.open('', '_blank');
    save({ autosave: status !== 'published' ? true : false, publish: 'keep' }).then(function () {
      if (w) w.location = cfg.publicUrl;
    });
    if (status === 'published' && dirty) { setSaved('Previewing the live version. Press Update to publish your changes.'); }
  }

  /* ---------- publish box ---------- */
  function renderPublish() {
    var box = $('bePublish'); box.innerHTML = '';
    var prev = h('button', { type: 'button', class: 'btn btn-outline-secondary', text: status === 'published' ? 'View live' : 'Preview' });
    prev.addEventListener('click', function () { if (status === 'published') window.open(cfg.publicUrl, '_blank'); else preview(); });

    if (status === 'published') {
      var upd = h('button', { type: 'button', class: 'btn btn-primary', text: 'Update' });
      upd.addEventListener('click', function () { save({ publish: 'keep' }).then(function (ok) { if (ok) announce('Post updated'); }); });
      var un = h('button', { type: 'button', class: 'btn btn-outline-secondary', text: 'Switch to draft' });
      un.addEventListener('click', function () {
        window.feConfirm({ title: 'Take this post offline?', message: 'It will disappear from the blog and search until you publish it again.', action: 'Switch to draft', danger: false })
          .then(function (yes) { if (yes) save({ publish: 'draft' }); });
      });
      box.appendChild(h('p', { class: 'be-pub-note', text: 'This post is live. Edits go live when you press Update.' }));
      box.appendChild(h('div', { class: 'be-pub-actions' }, [upd, prev, un]));
      return;
    }

    var pub = h('button', { type: 'button', class: 'btn btn-primary', text: 'Publish now' });
    pub.addEventListener('click', function () {
      if (isBlank({ type: 'heading', content: $('beTitle').value }) || !blocks.some(function (b) { return !isBlank(b); })) {
        setSaved('Add a title and some content before publishing.', true); return;
      }
      save({ publish: 'publish' });
    });
    var when = h('input', { type: 'datetime-local', class: 'form-control form-control-sm', 'aria-label': 'Publish date and time', value: cfg.publishedAt || '' });
    var sch = h('button', { type: 'button', class: 'btn btn-outline-secondary', text: status === 'scheduled' ? 'Update schedule' : 'Schedule' });
    sch.addEventListener('click', function () {
      if (!when.value) { setSaved('Pick a date and time first.', true); return; }
      save({ publish: 'schedule', published_at: when.value });
    });
    box.appendChild(h('p', { class: 'be-pub-note', text: status === 'scheduled' ? 'Scheduled. It goes live on the date below.' : 'Draft. Changes save automatically.' }));
    box.appendChild(h('div', { class: 'be-pub-actions' }, [status === 'scheduled' ? sch : pub, prev]));
    box.appendChild(h('div', { class: 'be-schedule' }, [h('label', { class: 'form-label', text: status === 'scheduled' ? 'Goes live' : 'Or schedule for later' }), when, status === 'scheduled' ? null : sch]));
    if (status === 'scheduled') {
      var back = h('button', { type: 'button', class: 'btn btn-link btn-sm p-0 mt-2', text: 'Cancel schedule and keep as draft' });
      back.addEventListener('click', function () { save({ publish: 'draft' }); });
      box.appendChild(back);
    }
  }

  /* ---------- keyboard + leave guard ---------- */
  document.addEventListener('keydown', function (e) {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') { e.preventDefault(); save({ publish: 'keep', autosave: status !== 'published' }); }
  });
  window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

  /* go */
  growTitle();
  // A new post opens with one empty text block so you can start typing straight away.
  if (!blocks.length) blocks.push(prep({ id: null, type: 'paragraph', content: '', extras: {} }));
  setBadge();
  renderPublish();
  render();
  setSaved(status === 'draft' && !blocks.length ? '' : '');
})();
