(function () {
  "use strict";

  const form = document.getElementById("petition-form");
  const statusEl = document.getElementById("form-status");
  const submitBtn = document.getElementById("submit-btn");
  const sigList = document.getElementById("sig-list");
  const liveCount = document.getElementById("live-count");
  const heroCount = document.querySelector(".hero__count");
  const copyBtn = document.getElementById("copy-link");
  const shareUrl = document.getElementById("share-url");

  function formatCount(n) {
    try {
      return new Intl.NumberFormat().format(n);
    } catch (_) {
      return String(n);
    }
  }

  function animateCount(el, to) {
    if (!el) return;
    const from = parseInt(String(el.textContent).replace(/,/g, ""), 10) || 0;
    if (from === to) {
      el.textContent = formatCount(to);
      return;
    }
    const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduced) {
      el.textContent = formatCount(to);
      return;
    }
    const start = performance.now();
    const duration = 700;
    function frame(now) {
      const t = Math.min(1, (now - start) / duration);
      const eased = 1 - Math.pow(1 - t, 3);
      el.textContent = formatCount(Math.round(from + (to - from) * eased));
      if (t < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  function clearFieldErrors() {
    document.querySelectorAll(".field__error").forEach((el) => {
      el.hidden = true;
      el.textContent = "";
    });
    document.querySelectorAll(".field input").forEach((input) => {
      input.classList.remove("invalid");
    });
  }

  function showFieldErrors(fields) {
    Object.entries(fields || {}).forEach(([name, message]) => {
      const input = form.querySelector(`[name="${name}"]`);
      const err = form.querySelector(`[data-error-for="${name}"]`);
      if (input) input.classList.add("invalid");
      if (err) {
        err.textContent = message;
        err.hidden = false;
      }
    });
  }

  function setStatus(message, ok) {
    if (!statusEl) return;
    statusEl.hidden = !message;
    statusEl.textContent = message || "";
    statusEl.classList.toggle("is-ok", !!ok);
    statusEl.classList.toggle("is-error", !ok && !!message);
  }

  function renderRecent(recent) {
    if (!sigList || !Array.isArray(recent)) return;
    sigList.innerHTML = "";
    if (recent.length === 0) {
      const li = document.createElement("li");
      li.className = "sig-list__empty";
      li.id = "sig-empty";
      li.textContent = "Be the first to sign.";
      sigList.appendChild(li);
      return;
    }
    recent.forEach((row) => {
      const li = document.createElement("li");
      const name = document.createElement("span");
      name.className = "sig-list__name";
      name.textContent = row.character_name || "";
      const time = document.createElement("time");
      if (row.created_at) time.setAttribute("datetime", row.created_at);
      time.textContent = row.when || "";
      li.appendChild(name);
      li.appendChild(time);
      sigList.appendChild(li);
    });
  }

  // Hero count entrance animation on load
  if (heroCount) {
    const target = parseInt(heroCount.getAttribute("data-count") || "0", 10) || 0;
    heroCount.textContent = "0";
    animateCount(heroCount, target);
  }

  if (copyBtn && shareUrl) {
    copyBtn.addEventListener("click", async () => {
      const value = shareUrl.value;
      try {
        await navigator.clipboard.writeText(value);
        copyBtn.textContent = "Copied";
        setTimeout(() => {
          copyBtn.textContent = "Copy link";
        }, 1600);
      } catch (_) {
        shareUrl.select();
        document.execCommand("copy");
        copyBtn.textContent = "Copied";
        setTimeout(() => {
          copyBtn.textContent = "Copy link";
        }, 1600);
      }
    });
  }

  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    clearFieldErrors();
    setStatus("", false);

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Signing…";
    }

    const body = new FormData(form);

    try {
      const res = await fetch(form.action, {
        method: "POST",
        body,
        headers: { Accept: "application/json" },
      });
      const data = await res.json().catch(() => ({}));

      if (!res.ok || !data.ok) {
        if (data.fields) showFieldErrors(data.fields);
        setStatus(data.error || "Could not sign. Please try again.", false);
        if (window.turnstile && typeof window.turnstile.reset === "function") {
          window.turnstile.reset();
        }
        return;
      }

      form.reset();
      if (window.turnstile && typeof window.turnstile.reset === "function") {
        window.turnstile.reset();
      }
      setStatus(data.message || "Thank you. Your signature was recorded.", true);

      if (typeof data.count === "number") {
        animateCount(heroCount, data.count);
        if (liveCount) liveCount.textContent = formatCount(data.count);
        if (heroCount) heroCount.setAttribute("data-count", String(data.count));
      }
      if (data.recent) renderRecent(data.recent);
    } catch (_) {
      setStatus("Network error. Check your connection and try again.", false);
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = "Sign the petition";
      }
    }
  });
})();
