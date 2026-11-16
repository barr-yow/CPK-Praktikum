(function (global) {
  "use strict";

  const TOKEN_KEY = "pesankita_token";
  const DEFAULT_API_BASE = "http://localhost:8000/api";

  function getApiBase() {
    return localStorage.getItem("pesankita_api_base") || DEFAULT_API_BASE;
  }

  function getToken() {
    return localStorage.getItem(TOKEN_KEY);
  }

  function setToken(token) {
    if (token) {
      localStorage.setItem(TOKEN_KEY, token);
    } else {
      localStorage.removeItem(TOKEN_KEY);
    }
  }

  async function apiRequest(endpoint, options = {}) {
    const headers = {
      "Content-Type": "application/json",
      ...(options.headers || {}),
    };

    const token = getToken();
    if (token) {
      headers.Authorization = `Bearer ${token}`;
    }

    const fetchOptions = {
      method: options.method || "GET",
      headers,
    };

    if (options.body !== undefined) {
      fetchOptions.body = JSON.stringify(options.body);
    }

    const response = await fetch(`${getApiBase()}${endpoint}`, fetchOptions);
    const payload = await response.json().catch(() => ({
      success: false,
      message: "Respons server bukan JSON yang valid.",
      data: null,
    }));

    if (!response.ok || !payload.success) {
      const error = new Error(payload.message || "Terjadi kesalahan pada server.");
      error.status = response.status;
      error.payload = payload;
      throw error;
    }

    return payload;
  }

  global.ApiClient = {
    getToken,
    setToken,
    request: apiRequest,
  };
})(window);
