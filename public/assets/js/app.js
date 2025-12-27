
(function () {
  const appContent = () => document.getElementById('appContent');
  const appFlash = () => document.getElementById('appFlash');

  // keep safe escaping
  function escapeHtml(str) {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  // UPDATED: supports flashArea OR appFlash, still escapes text
  function showFlash(type, msg) {
    const flashArea =
      document.getElementById('flashArea') ||
      document.getElementById('appFlash');

    if (!flashArea) return;

    flashArea.innerHTML = `<div class="alert ${type}">${escapeHtml(msg)}</div>`;
    setTimeout(() => { flashArea.innerHTML = ''; }, 2500);
  }

  async function ajaxNavigate(url, push = true) {
    const u = new URL(url, window.location.href);
    if (u.origin !== window.location.origin) {
      window.location.href = url;
      return;
    }

    u.searchParams.set('partial', '1');

    const res = await fetch(u.toString(), {
      headers: { 'X-Requested-With': 'fetch' }
    });

    if (!res.ok) {
      window.location.href = url;
      return;
    }

    const html = await res.text();
    if (appContent()) appContent().innerHTML = html;

    if (push) history.pushState({}, '', url);

    // run page hooks after content load
    initPageFeatures();
  }

  function isInternalLink(a) {
    if (!a) return false;
    const href = a.getAttribute('href');
    const blocked = ['login.php', 'authenticate.php', 'logout.php'];

    if (!href) return false;
    if (href.startsWith('http') || href.startsWith('mailto:') || href.startsWith('#')) return false;
    if (href.endsWith('.css') || href.endsWith('.js')) return false;
    if (blocked.some(b => href.includes(b))) return false;
    return true;
  }

  document.addEventListener('click', (e) => {
    const a = e.target.closest('a');
    if (!a) return;

    if (isInternalLink(a)) {
      e.preventDefault();
      ajaxNavigate(a.getAttribute('href'));
    }
  });

  window.addEventListener('popstate', () => {
    ajaxNavigate(window.location.href, false);
  });

  window.DolphinApp = {
    navigate: ajaxNavigate,
    flash: showFlash,
    escape: escapeHtml
  };

 
  //  Dashboard AJAX filter
  function initDashboardFilters() {
    const tbody = document.querySelector('#contactsTbody');
    if (!tbody) return;

    document.querySelectorAll('[data-filter-link]').forEach(link => {
      link.addEventListener('click', async (e) => {
        e.preventDefault();
        const filter = link.getAttribute('data-filter');

        const res = await fetch(`api/contacts_list.php?filter=${encodeURIComponent(filter)}`, {
          headers: { 'X-Requested-With': 'fetch' }
        });

        const json = await res.json();
        if (!json.ok) return showFlash('error', json.message || 'Failed to load contacts.');

        tbody.innerHTML = '';
        json.data.contacts.forEach(c => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${escapeHtml(c.name)}</td>
            <td>${escapeHtml(c.email)}</td>
            <td>${escapeHtml(c.company)}</td>
            <td><span class="badge ${c.type === 'Sales Lead' ? 'badge-sales' : 'badge-support'}">${escapeHtml(c.type)}</span></td>
            <td><a href="contact.php?id=${c.id}">View</a></td>
          `;
          tbody.appendChild(tr);
        });

        document.querySelectorAll('[data-filter-link]').forEach(x => x.classList.remove('active'));
        link.classList.add('active');
      });
    });
  }


  // New contact AJAX
  function initNewContactAjax() {
    const form = document.querySelector('#newContactForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const fd = new FormData(form);
      const res = await fetch('api/contacts_create.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'fetch' },
        body: fd
      });

      const json = await res.json();
      if (!json.ok) return showFlash('error', json.message);

      showFlash('success', json.message);
      setTimeout(() => window.DolphinApp.navigate('dashboard.php'), 600);
    });
  }


  //  New user AJAX
  function initNewUserAjax() {
    const form = document.querySelector('#newUserForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const fd = new FormData(form);
      const res = await fetch('api/users_create.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'fetch' },
        body: fd
      });

      const json = await res.json();
      if (!json.ok) return showFlash('error', json.message);

      showFlash('success', json.message);
      setTimeout(() => window.DolphinApp.navigate('users.php'), 600);
    });
  }

  //  Contact page AJAX actions (Assign / Toggle / Add Note)
  async function postForm(url, dataObj) {
    const formData = new URLSearchParams();
    Object.entries(dataObj).forEach(([k, v]) => formData.append(k, v));

    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: formData.toString()
    });

    return res.json();
  }

  function initContactPage(root) {
    const contactId = root.dataset.contactId;

    const assignBtn = root.querySelector('#assignBtn');
    const toggleTypeBtn = root.querySelector('#toggleTypeBtn');
    const assignedToValue = root.querySelector('#assignedToValue');

    const noteForm = root.querySelector('#noteForm');
    const noteTextarea = root.querySelector('#noteComment');
    const notesList = root.querySelector('#notesList');

    if (assignBtn) {
      assignBtn.addEventListener('click', async () => {
        const json = await postForm('api/contact_action.php', {
          action: 'assign_to_me',
          contact_id: contactId
        });

        if (!json.ok) return showFlash('error', json.message);

        if (assignedToValue) assignedToValue.textContent = json.data.assigned_to_name;
        showFlash('success', json.message);
      });
    }

    if (toggleTypeBtn) {
      toggleTypeBtn.addEventListener('click', async () => {
        const json = await postForm('api/contact_action.php', {
          action: 'toggle_type',
          contact_id: contactId
        });

        if (!json.ok) return showFlash('error', json.message);

        toggleTypeBtn.innerHTML = `<i class="fa-solid fa-repeat"></i> ${escapeHtml(json.data.toggle_label)}`;
        showFlash('success', json.message);
      });
    }

    if (noteForm) {
      noteForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const comment = (noteTextarea?.value || '').trim();
        if (!comment) return showFlash('error', 'Note cannot be empty.');

        const json = await postForm('api/notes_add.php', {
          contact_id: contactId,
          comment
        });

        if (!json.ok) return showFlash('error', json.message);

        const muted = notesList?.querySelector('.muted');
        if (muted) muted.remove();

        const div = document.createElement('div');
        div.className = 'note-item';
        div.innerHTML = `
          <div class="note-author"></div>
          <div class="note-comment"></div>
          <div class="note-date"></div>
        `;

        div.querySelector('.note-author').textContent = json.data.author;
        div.querySelector('.note-comment').textContent = json.data.comment;
        div.querySelector('.note-date').textContent = json.data.created_at;

        if (notesList) notesList.prepend(div);
        if (noteTextarea) noteTextarea.value = '';

        showFlash('success', json.message);
      });
    }
  }

  function initPage() {
    const root = document.querySelector('#appContent .page');
    if (!root) return;

    const page = root.dataset.page;

    if (page === 'contact') initContactPage(root);
    // later:
    // if (page === 'dashboard') initDashboardPage(root);
    // if (page === 'users') initUsersPage(root);
  }


 //  Run initializers depending on what exists on the page
  function initPageFeatures() {
    initDashboardFilters();
    initNewContactAjax();
    initNewUserAjax();
    initPage(); // ✅ IMPORTANT: initializes contact page after AJAX loads
  }

  // Initial run
  document.addEventListener('DOMContentLoaded', initPageFeatures);
})();
