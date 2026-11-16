(function () {
  "use strict";

  const state = {
    user: null,
    contacts: [],
    toastTimer: null,
  };

  const els = {
    toast: document.getElementById("toast"),
    authView: document.getElementById("auth-view"),
    dashboardView: document.getElementById("dashboard-view"),
    tabLogin: document.getElementById("tab-login"),
    tabRegister: document.getElementById("tab-register"),
    loginForm: document.getElementById("login-form"),
    registerForm: document.getElementById("register-form"),
    userDisplayName: document.getElementById("user-display-name"),
    userDisplayHandle: document.getElementById("user-display-handle"),
    btnLogout: document.getElementById("btn-logout"),
    btnOpenDeleteAccount: document.getElementById("btn-open-delete-account"),
    searchContacts: document.getElementById("search-contacts"),
    btnNewContact: document.getElementById("btn-new-contact"),
    contactsContainer: document.getElementById("contacts-container"),
    contactModal: document.getElementById("contact-modal"),
    contactModalTitle: document.getElementById("contact-modal-title"),
    contactForm: document.getElementById("contact-form"),
    contactId: document.getElementById("contact-id"),
    contactName: document.getElementById("contact-name"),
    contactPhone: document.getElementById("contact-phone"),
    contactEmail: document.getElementById("contact-email"),
    contactNotes: document.getElementById("contact-notes"),
    btnCloseContactModal: document.getElementById("btn-close-contact-modal"),
    btnCancelContact: document.getElementById("btn-cancel-contact"),
    deleteAccountModal: document.getElementById("delete-account-modal"),
    deleteAccountForm: document.getElementById("delete-account-form"),
    deleteAccountPassword: document.getElementById("delete-account-password"),
    btnCloseDeleteAccount: document.getElementById("btn-close-delete-account"),
    btnCancelDeleteAccount: document.getElementById("btn-cancel-delete-account"),
  };

  function showToast(message, type = "success") {
    clearTimeout(state.toastTimer);
    els.toast.textContent = message;
    els.toast.className = `toast ${type}`;
    state.toastTimer = setTimeout(() => {
      els.toast.classList.add("hidden");
    }, 3500);
  }

  function switchAuthTab(mode) {
    const isLogin = mode === "login";
    els.tabLogin.classList.toggle("active", isLogin);
    els.tabRegister.classList.toggle("active", !isLogin);
    els.tabLogin.setAttribute("aria-selected", String(isLogin));
    els.tabRegister.setAttribute("aria-selected", String(!isLogin));
    els.loginForm.classList.toggle("hidden", !isLogin);
    els.registerForm.classList.toggle("hidden", isLogin);
  }

  function showAuthView() {
    state.user = null;
    state.contacts = [];
    els.dashboardView.classList.add("hidden");
    els.authView.classList.remove("hidden");
    els.loginForm.reset();
    els.registerForm.reset();
  }

  function showDashboardView(user) {
    state.user = user;
    els.userDisplayName.textContent = user.name;
    els.userDisplayHandle.textContent = `@${user.username} • ${user.email}`;
    els.authView.classList.add("hidden");
    els.dashboardView.classList.remove("hidden");
  }

  function openContactModal(contact = null) {
    els.contactForm.reset();
    if (contact) {
      els.contactModalTitle.textContent = "Edit Kontak";
      els.contactId.value = String(contact.id);
      els.contactName.value = contact.name || "";
      els.contactPhone.value = contact.phone || "";
      els.contactEmail.value = contact.email || "";
      els.contactNotes.value = contact.notes || "";
    } else {
      els.contactModalTitle.textContent = "Tambah Kontak";
      els.contactId.value = "";
    }
    els.contactModal.classList.remove("hidden");
    els.contactName.focus();
  }

  function closeContactModal() {
    els.contactModal.classList.add("hidden");
  }

  function openDeleteAccountModal() {
    els.deleteAccountForm.reset();
    els.deleteAccountModal.classList.remove("hidden");
    els.deleteAccountPassword.focus();
  }

  function closeDeleteAccountModal() {
    els.deleteAccountModal.classList.add("hidden");
  }

  function createContactCard(contact) {
    const card = document.createElement("article");
    card.className = "card contact-card";

    const info = document.createElement("div");

    const nameEl = document.createElement("h3");
    nameEl.className = "contact-name";
    nameEl.textContent = contact.name;
    info.appendChild(nameEl);

    if (contact.phone) {
      const phoneEl = document.createElement("p");
      phoneEl.className = "contact-detail";
      phoneEl.textContent = `Telp: ${contact.phone}`;
      info.appendChild(phoneEl);
    }

    if (contact.email) {
      const emailEl = document.createElement("p");
      emailEl.className = "contact-detail";
      emailEl.textContent = `Email: ${contact.email}`;
      info.appendChild(emailEl);
    }

    if (contact.notes) {
      const notesEl = document.createElement("p");
      notesEl.className = "contact-notes";
      notesEl.textContent = contact.notes;
      info.appendChild(notesEl);
    }

    const actions = document.createElement("div");
    actions.className = "contact-actions";

    const editBtn = document.createElement("button");
    editBtn.type = "button";
    editBtn.className = "btn btn-secondary btn-sm";
    editBtn.textContent = "Edit";
    editBtn.addEventListener("click", () => openContactModal(contact));

    const deleteBtn = document.createElement("button");
    deleteBtn.type = "button";
    deleteBtn.className = "btn btn-danger-outline btn-sm";
    deleteBtn.textContent = "Hapus";
    deleteBtn.addEventListener("click", () => handleDeleteContact(contact));

    actions.appendChild(editBtn);
    actions.appendChild(deleteBtn);

    card.appendChild(info);
    card.appendChild(actions);

    return card;
  }

  function renderContacts() {
    els.contactsContainer.innerHTML = "";

    if (state.contacts.length === 0) {
      const empty = document.createElement("div");
      empty.className = "empty-state";
      empty.textContent = els.searchContacts.value.trim()
        ? "Tidak ada kontak yang cocok dengan pencarian."
        : "Belum ada kontak. Klik \"+ Tambah Kontak\" untuk membuat kontak baru.";
      els.contactsContainer.appendChild(empty);
      return;
    }

    const fragment = document.createDocumentFragment();
    state.contacts.forEach((contact) => {
      fragment.appendChild(createContactCard(contact));
    });
    els.contactsContainer.appendChild(fragment);
  }

  async function loadContacts(query = "") {
    const encoded = query ? `?q=${encodeURIComponent(query)}` : "";
    const res = await ApiClient.request(`/contacts/index.php${encoded}`);
    state.contacts = res.data.contacts || [];
    renderContacts();
  }

  async function handleLogin(event) {
    event.preventDefault();
    const identifier = document.getElementById("login-identifier").value.trim();
    const password = document.getElementById("login-password").value;

    try {
      const res = await ApiClient.request("/auth/login.php", {
        method: "POST",
        body: { identifier, password },
      });
      ApiClient.setToken(res.data.token);
      showDashboardView(res.data.user);
      await loadContacts();
      showToast(res.message);
    } catch (err) {
      showToast(err.message, "error");
    }
  }

  async function handleRegister(event) {
    event.preventDefault();
    const name = document.getElementById("reg-name").value.trim();
    const username = document.getElementById("reg-username").value.trim();
    const email = document.getElementById("reg-email").value.trim();
    const password = document.getElementById("reg-password").value;

    try {
      const res = await ApiClient.request("/auth/register.php", {
        method: "POST",
        body: { name, username, email, password },
      });
      ApiClient.setToken(res.data.token);
      showDashboardView(res.data.user);
      await loadContacts();
      showToast(res.message);
    } catch (err) {
      showToast(err.message, "error");
    }
  }

  async function handleSaveContact(event) {
    event.preventDefault();
    const id = els.contactId.value.trim();
    const payload = {
      name: els.contactName.value.trim(),
      phone: els.contactPhone.value.trim(),
      email: els.contactEmail.value.trim(),
      notes: els.contactNotes.value.trim(),
    };

    try {
      let res;
      if (id) {
        res = await ApiClient.request("/contacts/update.php", {
          method: "PUT",
          body: { id: Number(id), ...payload },
        });
      } else {
        res = await ApiClient.request("/contacts/create.php", {
          method: "POST",
          body: payload,
        });
      }
      closeContactModal();
      await loadContacts(els.searchContacts.value.trim());
      showToast(res.message);
    } catch (err) {
      showToast(err.message, "error");
    }
  }

  async function handleDeleteContact(contact) {
    const confirmed = window.confirm(`Hapus kontak "${contact.name}"?`);
    if (!confirmed) {
      return;
    }

    try {
      const res = await ApiClient.request("/contacts/delete.php", {
        method: "DELETE",
        body: { id: Number(contact.id) },
      });
      await loadContacts(els.searchContacts.value.trim());
      showToast(res.message);
    } catch (err) {
      showToast(err.message, "error");
    }
  }

  async function handleDeleteAccount(event) {
    event.preventDefault();
    const password = els.deleteAccountPassword.value;

    try {
      const res = await ApiClient.request("/auth/delete.php", {
        method: "DELETE",
        body: { password },
      });
      closeDeleteAccountModal();
      ApiClient.setToken(null);
      showAuthView();
      showToast(res.message);
    } catch (err) {
      showToast(err.message, "error");
    }
  }

  function handleLogout() {
    ApiClient.setToken(null);
    showAuthView();
    showToast("Berhasil keluar dari akun.");
  }

  async function initSession() {
    const token = ApiClient.getToken();
    if (!token) {
      showAuthView();
      return;
    }

    try {
      const res = await ApiClient.request("/auth/me.php");
      showDashboardView(res.data.user);
      await loadContacts();
    } catch (_err) {
      ApiClient.setToken(null);
      showAuthView();
    }
  }

  function bindEvents() {
    els.tabLogin.addEventListener("click", () => switchAuthTab("login"));
    els.tabRegister.addEventListener("click", () => switchAuthTab("register"));
    els.loginForm.addEventListener("submit", handleLogin);
    els.registerForm.addEventListener("submit", handleRegister);
    els.btnLogout.addEventListener("click", handleLogout);

    els.btnNewContact.addEventListener("click", () => openContactModal());
    els.btnCloseContactModal.addEventListener("click", closeContactModal);
    els.btnCancelContact.addEventListener("click", closeContactModal);
    els.contactForm.addEventListener("submit", handleSaveContact);

    els.btnOpenDeleteAccount.addEventListener("click", openDeleteAccountModal);
    els.btnCloseDeleteAccount.addEventListener("click", closeDeleteAccountModal);
    els.btnCancelDeleteAccount.addEventListener("click", closeDeleteAccountModal);
    els.deleteAccountForm.addEventListener("submit", handleDeleteAccount);

    let searchTimer = null;
    els.searchContacts.addEventListener("input", () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        loadContacts(els.searchContacts.value.trim()).catch((err) => {
          showToast(err.message, "error");
        });
      }, 250);
    });
  }

  bindEvents();
  initSession();
})();
