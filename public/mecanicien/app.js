/* AutoGest Atelier — application mobile des mécaniciens (sans dépendance). */
(function () {
  'use strict';

  const API = location.origin + '/api';
  const $ = (s, root = document) => root.querySelector(s);
  const $$ = (s, root = document) => [...root.querySelectorAll(s)];

  const store = {
    get(k, d = null) { try { const v = localStorage.getItem(k); return v === null ? d : v; } catch { return d; } },
    set(k, v) { try { localStorage.setItem(k, v); } catch {} },
    del(k) { try { localStorage.removeItem(k); } catch {} },
  };

  const state = {
    token: store.get('ag_token'),
    user: JSON.parse(store.get('ag_user', 'null')),
    tasks: [],
    done: [],
    filter: 'all',
    query: '',
    knownIds: null,
    current: null,
    photo: null,
    installEvt: null,
  };

  /* ── Utilitaires ─────────────────────────────────────────────── */
  // Tout texte venant du serveur passe par esc() avant d'entrer dans le HTML
  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const icon = (name, cls = '') => `<svg class="ic ${cls}"><use href="#i-${name}"/></svg>`;
  const digits = (tel) => String(tel || '').replace(/[^\d+]/g, '');
  const waNumber = (tel) => digits(tel).replace(/^\+/, '').replace(/^00/, '');
  const isToday = (iso) => { if (!iso) return false; const d = new Date(iso), n = new Date(); return d.toDateString() === n.toDateString(); };
  const plural = (n, s, p) => `${n} ${n > 1 ? p : s}`;
  const vibrate = (p) => { if (store.get('ag_vibrate', '1') === '1' && navigator.vibrate) navigator.vibrate(p); };

  const STATUS = {
    en_cours:          { label: 'En cours',          icon: 'play',  hint: 'Je travaille dessus' },
    en_attente_pieces: { label: 'Attente pièces',    icon: 'box',   hint: 'Pièce manquante' },
    probleme:          { label: 'Problème',          icon: 'alert', hint: 'Besoin du patron' },
    termine:           { label: 'Terminé',           icon: 'check', hint: 'Véhicule prêt' },
  };
  const FILTERS = [
    ['all', 'Toutes'], ['urgent', 'Urgentes'], ['nouveau', 'Nouvelles'],
    ['en_cours', 'En cours'], ['en_attente_pieces', 'Attente pièces'], ['probleme', 'Problème'],
  ];
  const QUICK = ['Diagnostic effectué', 'Pièce commandée', 'Pièce reçue, montage en cours', 'Essai routier OK', 'Client à rappeler'];

  const pill = (statut, label) => `<span class="pill s-${esc(statut)}">${esc(label)}</span>`;
  const urgTag = (u) => (u === 'urgent' ? '<span class="tag urgent">URGENT</span>' : u === 'vip' ? '<span class="tag vip">VIP</span>' : '');

  /* ── Notifications ───────────────────────────────────────────── */
  function toast(msg, type = 'success') {
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');
    el.innerHTML = `<span class="t-icon t-ico">${icon(type === 'error' ? 'x' : type === 'info' ? 'bell' : 'check-sm', 'sm')}</span><span></span>`;
    el.lastChild.textContent = msg;
    $('#toasts').appendChild(el);
    setTimeout(() => { el.classList.add('out'); el.addEventListener('animationend', () => el.remove(), { once: true }); }, type === 'error' ? 4500 : 2800);
  }

  /* ── Thème (même préférence que l'espace bureau) ─────────────── */
  const media = matchMedia('(prefers-color-scheme: dark)');
  function applyTheme(pref, save = false) {
    const dark = pref === 'dark' || (pref === 'system' && media.matches);
    document.documentElement.dataset.theme = dark ? 'dark' : 'light';
    $('meta[name="theme-color"]').content = dark ? '#10131a' : '#ffffff';
    store.set('ag-theme', pref);
    $$('#theme-seg button').forEach((b) => { const on = b.dataset.themePref === pref; b.classList.toggle('on', on); b.setAttribute('aria-checked', on); });
    $$('[data-action="toggle-theme"] use').forEach((u) => u.setAttribute('href', dark ? '#i-sun' : '#i-moon'));
    if (save && state.token) api('/preferences', { method: 'PATCH', json: { theme: pref } }).catch(() => {});
  }
  media.addEventListener('change', () => { if (store.get('ag-theme', 'system') === 'system') applyTheme('system'); });

  /* ── API ─────────────────────────────────────────────────────── */
  async function api(path, { method = 'GET', json, form } = {}) {
    const headers = { Accept: 'application/json' };
    if (state.token) headers.Authorization = 'Bearer ' + state.token;
    let body;
    if (json) { headers['Content-Type'] = 'application/json'; body = JSON.stringify(json); }
    if (form) body = form;
    const res = await fetch(API + path, { method, headers, body });
    let data = null;
    try { data = await res.json(); } catch {}
    if (res.status === 401 && path !== '/login') { logout(data?.message || 'Session expirée, reconnectez-vous.'); throw new Error('401'); }
    if (!res.ok) {
      const msg = data?.message || (data?.errors ? Object.values(data.errors)[0][0] : 'Erreur ' + res.status);
      const err = new Error(msg); err.status = res.status; throw err;
    }
    return data;
  }

  /* ── Navigation ──────────────────────────────────────────────── */
  function show(screen) { $$('.screen').forEach((s) => s.classList.toggle('active', s.id === 'screen-' + screen)); }
  function tab(name) {
    $$('.tab').forEach((t) => t.classList.toggle('active', t.id === 'tab-' + name));
    $$('.tabbar button').forEach((b) => b.classList.toggle('on', b.dataset.tab === name));
    $('#scroll').scrollTop = 0;
    if (name === 'profile') renderProfile();
  }

  /* ── Connexion ───────────────────────────────────────────────── */
  async function login(e) {
    e.preventDefault();
    const email = $('#email').value.trim(), password = $('#password').value;
    const err = $('#login-error'), btn = $('#login-btn');
    err.hidden = true;
    if (!email || !password) { err.hidden = false; err.lastChild.textContent = 'Saisissez votre e-mail et votre mot de passe.'; return; }
    btn.classList.add('loading'); btn.disabled = true;
    try {
      const data = await api('/login', { method: 'POST', json: { email, password } });
      state.token = data.token; state.user = data.user;
      store.set('ag_token', data.token); store.set('ag_user', JSON.stringify(data.user));
      if (data.user.theme) applyTheme(data.user.theme);
      $('#password').value = '';
      enterApp();
      vibrate(30);
    } catch (ex) {
      err.hidden = false;
      err.lastChild.textContent = ex.status ? ex.message : 'Impossible de joindre le serveur. Vérifiez votre connexion.';
    } finally { btn.classList.remove('loading'); btn.disabled = false; }
  }

  async function logout(message) {
    if (state.token && !message) api('/logout', { method: 'POST' }).catch(() => {});
    state.token = null; state.user = null; state.tasks = []; state.done = []; state.knownIds = null;
    store.del('ag_token'); store.del('ag_user');
    show('login');
    if (message) toast(message, 'error');
  }

  function enterApp() {
    const u = state.user || {};
    const first = (u.name || 'Mécanicien').split(' ')[0];
    $('#hello-name').textContent = 'Bonjour ' + first;
    $('#today').textContent = new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
    setAvatar($('#header-avatar'), u);
    show('app');
    const start = new URLSearchParams(location.search).get('tab');
    tab(['tasks', 'done', 'profile'].includes(start) ? start : 'tasks');
    load();
  }

  function setAvatar(el, u) {
    if (u?.avatar) { el.innerHTML = `<img src="${esc(u.avatar)}" alt="" referrerpolicy="no-referrer">`; }
    else { el.textContent = u?.initiales || (u?.name || '?').charAt(0).toUpperCase(); }
  }

  /* ── Chargement des interventions ────────────────────────────── */
  let loading = false;
  async function load({ silent = false } = {}) {
    if (loading || !state.token) return;
    loading = true;
    if (!silent && !state.tasks.length) $('#list').innerHTML = '<div class="skel"></div><div class="skel"></div><div class="skel"></div>';
    try {
      const [tasks, done] = await Promise.all([api('/mes-taches'), api('/mes-taches-terminees')]);
      const ids = new Set(tasks.map((t) => t.id));
      if (state.knownIds) {
        const fresh = tasks.filter((t) => !state.knownIds.has(t.id));
        if (fresh.length) { toast(fresh.length > 1 ? `${fresh.length} nouvelles interventions` : `Nouvelle intervention : ${fresh[0].numero}`, 'info'); vibrate([60, 60, 60]); }
      }
      state.knownIds = ids;
      state.tasks = tasks; state.done = done;
      store.set('ag_cache', JSON.stringify({ tasks, done }));
      render();
    } catch (ex) {
      if (ex.message === '401') return;
      const cache = JSON.parse(store.get('ag_cache', 'null'));
      if (cache && !state.tasks.length) { state.tasks = cache.tasks; state.done = cache.done; render(); }
      if (!state.tasks.length) $('#list').innerHTML = emptyHTML('wifi-off', 'Connexion impossible', 'Vérifiez votre réseau puis réessayez.', true, 'var(--soft-yellow)', 'var(--yellow)');
      else if (!silent) toast('Impossible d\'actualiser. Données hors ligne affichées.', 'error');
    } finally { loading = false; }
  }

  function render() {
    renderStats(); renderChips(); renderList(); renderDone();
    const n = state.tasks.length;
    const badge = $('#badge-tasks');
    badge.hidden = !n; badge.textContent = n;
  }

  function counts() {
    const c = { all: state.tasks.length, urgent: 0 };
    state.tasks.forEach((t) => { c[t.statut] = (c[t.statut] || 0) + 1; if (t.urgence !== 'normal') c.urgent++; });
    return c;
  }

  function renderStats() {
    const c = counts();
    const today = state.done.filter((t) => isToday(t.date_sortie_iso)).length;
    $('#st-actives').textContent = c.all;
    $('#st-urgent').textContent = c.urgent;
    $('#st-today').textContent = today;
  }

  function renderChips() {
    const c = counts();
    $('#chips').innerHTML = FILTERS.filter(([k]) => k === 'all' || c[k]).map(([k, label]) =>
      `<button class="chip ${state.filter === k ? 'on' : ''}" data-filter="${k}" role="tab" aria-selected="${state.filter === k}">${label}<span class="n">${c[k] || 0}</span></button>`
    ).join('');
  }

  function filtered() {
    const q = state.query.trim().toLowerCase();
    return state.tasks.filter((t) => {
      if (state.filter === 'urgent' && t.urgence === 'normal') return false;
      if (!['all', 'urgent'].includes(state.filter) && t.statut !== state.filter) return false;
      if (!q) return true;
      const hay = [t.numero, t.vehicle?.immatriculation, t.vehicle?.marque, t.vehicle?.modele, t.client?.nom, t.client?.prenom, t.description_panne].join(' ').toLowerCase();
      return hay.includes(q) || hay.replace(/[\s-]/g, '').includes(q.replace(/[\s-]/g, ''));
    });
  }

  function cardHTML(t, i = 0) {
    const v = t.vehicle || {}, c = t.client || {};
    const car = [v.marque, v.modele].filter(Boolean).join(' ');
    const extra = [v.annee, v.couleur].filter(Boolean).join(' · ');
    return `
      <article class="task u-${esc(t.urgence)}" data-open="${Number(t.id)}" tabindex="0" style="animation-delay:${Math.min(i, 8) * 40}ms">
        <div class="task-top"><span class="task-num">${esc(t.numero)}</span>${urgTag(t.urgence)}${pill(t.statut, t.statut_label)}</div>
        <div class="plate-row">
          <span class="plate">${esc(v.immatriculation || '—')}</span>
          <div class="car"><b>${esc(car || 'Véhicule')}</b>${esc(extra)}</div>
        </div>
        <div class="task-desc">${esc(t.description_panne)}</div>
        <div class="task-foot">
          <div class="who">${icon('user', 'sm')}<span>${esc(`${c.prenom || ''} ${c.nom || ''}`.trim() || '—')}</span></div>
          <span>${icon('calendar', 'sm')} ${esc(t.date_sortie || t.date_entree || '')}</span>
          ${c.telephone ? `<a class="call" href="tel:${esc(digits(c.telephone))}" data-stop aria-label="Appeler le client">${icon('phone', 'sm')}</a>` : ''}
        </div>
      </article>`;
  }

  function emptyHTML(ic, title, sub, retry = false, bg = 'var(--soft-green)', fg = 'var(--green)') {
    return `<div class="empty"><div class="e-ico" style="background:${bg};color:${fg}">${icon(ic)}</div><h3>${title}</h3><p>${sub}</p>${retry ? '<button class="btn btn-soft" data-action="refresh">Réessayer</button>' : ''}</div>`;
  }

  function renderList() {
    const list = filtered();
    $('#list-title').textContent = state.query ? plural(list.length, 'résultat', 'résultats') : plural(list.length, 'intervention', 'interventions');
    if (!state.tasks.length) { $('#list').innerHTML = emptyHTML('check', 'Tout est à jour !', 'Aucune intervention en attente. Le patron vous en assignera bientôt.'); return; }
    if (!list.length) { $('#list').innerHTML = emptyHTML('search', 'Aucun résultat', 'Essayez un autre filtre ou une autre recherche.', false, 'var(--soft-gray)', 'var(--t3)'); return; }
    $('#list').innerHTML = list.map(cardHTML).join('');
  }

  function renderDone() {
    $('#done-list').innerHTML = state.done.length
      ? state.done.map(cardHTML).join('')
      : emptyHTML('history', 'Rien pour l\'instant', 'Vos interventions terminées apparaîtront ici.', false, 'var(--soft-gray)', 'var(--t3)');
  }

  function renderProfile() {
    const u = state.user || {};
    setAvatar($('#profile-avatar'), u);
    $('#profile-name').textContent = u.name || '—';
    $('#profile-email').textContent = u.email || '';
    $('#pf-actives').textContent = state.tasks.length;
    $('#pf-today').textContent = state.done.filter((t) => isToday(t.date_sortie_iso)).length;
    $('#pf-done').textContent = state.done.length;
    $('#sw-vibrate').setAttribute('aria-checked', store.get('ag_vibrate', '1') === '1');
    applyTheme(store.get('ag-theme', 'system'));
  }

  /* ── Détail ──────────────────────────────────────────────────── */
  async function openDetail(id, push = true) {
    if (push) history.pushState({ detail: id }, '', '#ordre-' + id);
    state.photo = null;
    const summary = [...state.tasks, ...state.done].find((t) => t.id === id);
    $('#d-num').textContent = summary?.numero || '…';
    $('#d-sub').textContent = summary ? `Entrée le ${summary.date_entree}` : '';
    $('#d-status').innerHTML = summary ? pill(summary.statut, summary.statut_label) : '';
    $('#d-body').innerHTML = '<div class="skel" style="height:190px"></div><div class="skel" style="height:110px;margin-top:12px"></div><div class="skel" style="height:260px;margin-top:12px"></div>';
    show('detail');
    try {
      const t = await api('/taches/' + id);
      state.current = t;
      renderDetail(t);
    } catch (ex) {
      if (ex.message !== '401') $('#d-body').innerHTML = emptyHTML('wifi-off', 'Chargement impossible', esc(ex.message), false, 'var(--soft-yellow)', 'var(--yellow)');
    }
  }

  function renderDetail(t) {
    const v = t.vehicle || {}, c = t.client || {};
    const closed = ['termine', 'annule'].includes(t.statut);
    $('#d-num').textContent = t.numero;
    $('#d-sub').textContent = `Entrée le ${t.date_entree}${t.date_sortie_prevue ? ' · sortie prévue ' + t.date_sortie_prevue : ''}`;
    $('#d-status').innerHTML = pill(t.statut, t.statut_label);
    const meta = [
      v.annee && `${icon('calendar', 'sm')}${esc(v.annee)}`,
      v.couleur && `<span style="width:10px;height:10px;border-radius:50%;border:1px solid var(--border-2);background:var(--sunken)"></span>${esc(v.couleur)}`,
      t.kilometrage_entree && `${icon('gauge', 'sm')}${Number(t.kilometrage_entree).toLocaleString('fr-FR')} km`,
    ].filter(Boolean).map((m) => `<span>${m}</span>`).join('');
    const tel = c.telephone ? digits(c.telephone) : '';
    const statusOpts = Object.entries(STATUS).map(([k, s]) => `
      <label class="status-opt c-${k}">
        <input type="radio" name="statut" value="${k}" ${(t.statut === k || (k === 'en_cours' && !STATUS[t.statut])) ? 'checked' : ''}>
        <span><i>${icon(s.icon, 'sm')}</i><b>${s.label}</b></span>
      </label>`).join('');

    $('#d-body').innerHTML = `
      <div class="hero">
        <div style="display:flex;align-items:center;gap:8px">${urgTag(t.urgence)}</div>
        <span class="plate">${esc(v.immatriculation || '—')}</span>
        <h2>${esc([v.marque, v.modele].filter(Boolean).join(' ') || 'Véhicule')}</h2>
        <div class="meta">${meta}</div>
      </div>

      <div class="actions">
        <a class="action" href="${tel ? 'tel:' + esc(tel) : '#'}" ${tel ? '' : 'aria-disabled="true" style="opacity:.5"'}><span class="a-ico" style="background:var(--soft-green);color:var(--green)">${icon('phone')}</span>Appeler</a>
        <a class="action" href="${tel ? 'https://wa.me/' + esc(waNumber(tel)) : '#'}" target="_blank" rel="noopener" ${tel ? '' : 'aria-disabled="true" style="opacity:.5"'}><span class="a-ico" style="background:color-mix(in srgb,#25d366 15%,transparent);color:#1faa52">${icon('chat')}</span>WhatsApp</a>
        <a class="action" href="${tel ? 'sms:' + esc(tel) : '#'}" ${tel ? '' : 'aria-disabled="true" style="opacity:.5"'}><span class="a-ico" style="background:var(--soft-blue);color:var(--blue)">${icon('sms')}</span>SMS</a>
      </div>

      <div class="card">
        <h3>${icon('user', 'sm')} Client</h3>
        <p><b>${esc(`${c.prenom || ''} ${c.nom || ''}`.trim() || '—')}</b>${c.telephone ? '<br><span style="color:var(--t2)">' + esc(c.telephone) + '</span>' : ''}</p>
      </div>

      <div class="card">
        <h3>${icon('alert', 'sm')} Panne signalée</h3>
        <p>${esc(t.description_panne)}</p>
      </div>
      ${t.pieces_estimees ? `<div class="card note-accent"><h3 style="color:var(--accent)">${icon('box', 'sm')} Pièces prévues</h3><p>${esc(t.pieces_estimees)}</p></div>` : ''}
      ${t.notes_patron ? `<div class="card note-blue"><h3 style="color:var(--blue)">${icon('chat-boss', 'sm')} Consigne du patron</h3><p>${esc(t.notes_patron)}</p></div>` : ''}

      <form class="card" id="update-form" novalidate>
        <h3>${icon('note', 'sm')} ${closed ? 'Ajouter une note' : 'Mettre à jour'}</h3>
        ${closed ? '' : `<div class="status-grid">${statusOpts}</div>`}
        <div class="quick">${QUICK.map((q) => `<button type="button" data-quick="${esc(q)}">${esc(q)}</button>`).join('')}</div>
        <textarea class="textarea" id="u-note" placeholder="Travail effectué, problème rencontré…" maxlength="1000"></textarea>
        <div class="photo-pick">
          <label for="u-photo">${icon('camera')} Ajouter une photo</label>
          <input type="file" id="u-photo" accept="image/*" capture="environment" hidden>
          <div class="photo-prev" id="u-prev" hidden></div>
        </div>
        <div class="sticky-cta"><button class="btn btn-primary" type="submit" id="u-submit"><span>${icon('check')} ${closed ? 'Ajouter la note' : 'Enregistrer'}</span></button></div>
      </form>
      ${closed ? `<div class="done-banner">${icon('check', 'lg')} Intervention terminée${t.date_sortie ? ' le ' + esc(t.date_sortie) : ''}</div>` : ''}

      ${(t.notes || []).length ? `
      <div class="card">
        <h3>${icon('history', 'sm')} Historique · ${t.notes.length}</h3>
        <div class="timeline">${t.notes.map((n) => `
          <div class="ev">
            <div class="ev-head"><b>${esc(n.auteur)}</b><small>${esc(n.date)}</small></div>
            ${n.ancien_statut && n.nouveau_statut && n.ancien_statut !== n.nouveau_statut ? `<div class="ev-change">${pill(n.ancien_statut, n.ancien_label)}${icon('chevron', 'sm')}${pill(n.nouveau_statut, n.nouveau_label)}</div>` : ''}
            <p>${esc(n.contenu)}</p>
            ${n.photo_url ? `<a href="${esc(n.photo_url)}" target="_blank" rel="noopener"><img src="${esc(n.photo_url)}" alt="Photo de l'intervention" loading="lazy"></a>` : ''}
          </div>`).join('')}
        </div>
      </div>` : ''}`;
  }

  async function submitUpdate(e) {
    e.preventDefault();
    const t = state.current; if (!t) return;
    const closed = ['termine', 'annule'].includes(t.statut);
    const note = $('#u-note').value.trim();
    if (!note) { toast('Ajoutez un commentaire avant d\'enregistrer.', 'error'); $('#u-note').focus(); return; }
    const statut = closed ? null : $('input[name="statut"]:checked')?.value;
    const btn = $('#u-submit');
    btn.classList.add('loading'); btn.disabled = true;

    const form = new FormData();
    form.append('contenu', note);
    if (statut) form.append('statut', statut);
    if (state.photo) form.append('photo', state.photo, state.photo.name || 'photo.jpg');
    try {
      await api(`/taches/${t.id}/${closed ? 'note' : 'statut'}`, { method: 'POST', form });
      vibrate(40);
      toast(statut === 'termine' ? 'Bravo, intervention terminée !' : closed ? 'Note ajoutée.' : 'Mise à jour enregistrée.');
      await load({ silent: true });
      if (statut === 'termine') { history.back(); } else { openDetail(t.id, false); }
    } catch (ex) {
      if (ex.message !== '401') toast(ex.status ? ex.message : 'Pas de connexion. Réessayez plus tard.', 'error');
    } finally { btn.classList.remove('loading'); btn.disabled = false; }
  }

  // Réduit la photo avant l'envoi (réseau mobile)
  function compress(file, max = 1600, quality = 0.82) {
    return new Promise((resolve) => {
      const img = new Image();
      img.onload = () => {
        const scale = Math.min(1, max / Math.max(img.width, img.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(img.width * scale); canvas.height = Math.round(img.height * scale);
        canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
        canvas.toBlob((b) => resolve(b ? new File([b], 'photo.jpg', { type: 'image/jpeg' }) : file), 'image/jpeg', quality);
        URL.revokeObjectURL(img.src);
      };
      img.onerror = () => resolve(file);
      img.src = URL.createObjectURL(file);
    });
  }

  /* ── Tirer pour actualiser ───────────────────────────────────── */
  function pullToRefresh() {
    const scroller = $('#scroll'), ptr = $('#ptr');
    let startY = null, dist = 0;
    scroller.addEventListener('touchstart', (e) => { startY = scroller.scrollTop <= 0 ? e.touches[0].clientY : null; }, { passive: true });
    scroller.addEventListener('touchmove', (e) => {
      if (startY === null) return;
      dist = Math.max(0, Math.min(90, (e.touches[0].clientY - startY) * 0.5));
      ptr.style.height = dist + 'px';
      ptr.firstElementChild.style.transform = `rotate(${dist * 4}deg)`;
    }, { passive: true });
    scroller.addEventListener('touchend', async () => {
      if (startY === null) return;
      startY = null;
      if (dist > 60) {
        ptr.style.height = '56px'; ptr.classList.add('spinning'); vibrate(15);
        await load({ silent: true });
        ptr.classList.remove('spinning');
      }
      ptr.style.height = '0'; dist = 0;
    });
  }

  /* ── Événements ──────────────────────────────────────────────── */
  document.addEventListener('click', async (e) => {
    const el = e.target.closest('[data-action],[data-tab],[data-open],[data-filter],[data-quick],[data-theme-pref],#sw-vibrate,[data-stop]');
    if (!el) return;
    if (el.hasAttribute('data-stop')) { e.stopPropagation(); return; }
    if (el.dataset.tab) return tab(el.dataset.tab);
    if (el.dataset.open) return openDetail(Number(el.dataset.open));
    if (el.dataset.filter) { state.filter = el.dataset.filter; renderChips(); renderList(); return; }
    if (el.dataset.themePref) return applyTheme(el.dataset.themePref, true);
    if (el.dataset.quick) {
      const ta = $('#u-note');
      ta.value = ta.value ? ta.value.replace(/\s*$/, '') + '. ' + el.dataset.quick : el.dataset.quick;
      ta.focus(); return;
    }
    if (el.id === 'sw-vibrate') {
      const on = el.getAttribute('aria-checked') !== 'true';
      el.setAttribute('aria-checked', on); store.set('ag_vibrate', on ? '1' : '0'); if (on) vibrate(30); return;
    }
    switch (el.dataset.action) {
      case 'refresh': return load();
      case 'back': return history.length > 1 ? history.back() : show('app');
      case 'logout': return logout();
      case 'toggle-theme': return applyTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark', true);
      case 'reveal': {
        const input = $('#password'); const shown = input.type === 'password';
        input.type = shown ? 'text' : 'password'; el.setAttribute('aria-label', shown ? 'Masquer le mot de passe' : 'Afficher le mot de passe'); return;
      }
      case 'install':
        if (!state.installEvt) return;
        state.installEvt.prompt();
        await state.installEvt.userChoice;
        state.installEvt = null; $('#row-install').hidden = true; return;
      case 'remove-photo':
        state.photo = null; $('#u-prev').hidden = true; $('#u-prev').innerHTML = ''; $('#u-photo').value = ''; return;
    }
  });

  document.addEventListener('keydown', (e) => {
    const card = e.target.closest?.('[data-open]');
    if (card && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); openDetail(Number(card.dataset.open)); }
  });

  document.addEventListener('submit', (e) => {
    if (e.target.id === 'login-form') return login(e);
    if (e.target.id === 'update-form') return submitUpdate(e);
  });

  document.addEventListener('change', async (e) => {
    if (e.target.id !== 'u-photo' || !e.target.files[0]) return;
    state.photo = await compress(e.target.files[0]);
    const prev = $('#u-prev');
    prev.innerHTML = `<img src="${URL.createObjectURL(state.photo)}" alt="Aperçu"><button type="button" data-action="remove-photo" aria-label="Retirer la photo">${icon('x', 'sm')}</button>`;
    prev.hidden = false;
  });

  $('#q').addEventListener('input', (e) => { state.query = e.target.value; renderList(); });

  // Bouton « retour » d'Android : ferme le détail
  window.addEventListener('popstate', () => { if ($('#screen-detail').classList.contains('active')) show('app'); });

  // Hors ligne
  const setOnline = () => { $('#offline').hidden = navigator.onLine; };
  addEventListener('online', () => { setOnline(); load({ silent: true }); });
  addEventListener('offline', setOnline);

  // Actualisation automatique quand l'application est au premier plan
  setInterval(() => { if (document.visibilityState === 'visible' && state.token) load({ silent: true }); }, 60000);
  document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible' && state.token) load({ silent: true }); });

  // Installation
  addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); state.installEvt = e; $('#row-install').hidden = false; });
  addEventListener('appinstalled', () => { $('#row-install').hidden = true; toast('Application installée !'); });
  const standalone = matchMedia('(display-mode: standalone)').matches || navigator.standalone;
  if (/iphone|ipad|ipod/i.test(navigator.userAgent) && !standalone) $('#row-ios').hidden = false;

  if ('serviceWorker' in navigator) navigator.serviceWorker.register('/mecanicien/sw.js').catch(() => {});

  /* ── Démarrage ───────────────────────────────────────────────── */
  applyTheme(store.get('ag-theme', 'system'));
  setOnline();
  pullToRefresh();
  if (state.token) {
    enterApp();
    api('/me').then((u) => { state.user = u; store.set('ag_user', JSON.stringify(u)); setAvatar($('#header-avatar'), u); }).catch(() => {});
  } else {
    show('login');
  }
})();
