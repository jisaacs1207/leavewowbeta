(function () {
  "use strict";

  const form = document.getElementById("petition-form");
  const statusEl = document.getElementById("form-status");
  const submitBtn = document.getElementById("submit-btn");
  const sigList = document.getElementById("sig-list");
  const liveCount = document.getElementById("live-count");
  const heroCount = document.querySelector(".hero__count");
  const signCount = document.querySelector("[data-sign-count]");
  const copyBtn = document.getElementById("copy-link");
  const shareUrl = document.getElementById("share-url");
  const copySuggestedBtn = document.getElementById("copy-suggested");
  const suggestedCopy = document.getElementById("suggested-copy");
  const characterInput = document.getElementById("character_name");

  // Small client hint list; server filter is authoritative.
  const blockedHints = [
    "fuck", "shit", "asshole", "bitch", "cunt", "dick", "cock", "pussy",
    "nigger", "nigga", "faggot", "retard", "rape", "nazi", "hitler", "heil",
    "kike", "spic", "chink", "pedo", "porn", "xxx", "onlyfans", "loli",
    "towelhead", "raghead", "whitepower",
  ];

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

  function setCounts(n) {
    animateCount(heroCount, n);
    animateCount(signCount, n);
    const live = document.getElementById("live-count") || liveCount;
    if (live) live.textContent = formatCount(n);
    if (heroCount) heroCount.setAttribute("data-count", String(n));
    if (signCount) signCount.setAttribute("data-sign-count", String(n));
    if (sigList) sigList.setAttribute("data-count", String(n));
    const heroLabel = document.querySelector(".hero__meta-label");
    if (heroLabel) {
      heroLabel.textContent = n === 1 ? "signature" : "signatures";
    }
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

  function nameLooksAbusive(value) {
    const raw = String(value || "").trim().toLowerCase();
    if (!raw) return false;
    if (/https?:\/\/|www\.|@|\.(com|net|org|gg|io)\b/i.test(raw)) return true;
    if (/(.)\1{5,}/.test(raw)) return true;
    const compact = raw.replace(/[^a-z]+/g, "");
    return blockedHints.some((term) => {
      if (compact === term) return true;
      return new RegExp("\\b" + term.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + "\\b", "i").test(raw);
    });
  }

  function namesThreshold() {
    if (!sigList) return 25;
    const n = parseInt(sigList.getAttribute("data-names-threshold") || "25", 10);
    return Number.isFinite(n) && n > 0 ? n : 25;
  }

  function updateVoicesLede(count, visible) {
    const lede = document.getElementById("voices-lede");
    if (!lede) return;
    const countSpan = document.createElement("span");
    countSpan.id = "live-count";
    countSpan.textContent = formatCount(count);
    const sigWord = count === 1 ? "signature" : "signatures";
    lede.replaceChildren(
      countSpan,
      document.createTextNode(
        visible
          ? " character names so far. Newest first."
          : " " + sigWord + " so far. Names appear as more players sign."
      )
    );
  }

  function renderHeldNamesState() {
    if (!sigList) return;
    sigList.innerHTML = "";
    const li = document.createElement("li");
    li.className = "sig-list__empty";
    li.id = "sig-empty";
    li.appendChild(document.createTextNode("Names appear as more players sign. "));
    const signLink = document.createElement("a");
    signLink.href = "#sign";
    signLink.textContent = "Add your Forever character";
    li.appendChild(signLink);
    li.appendChild(document.createTextNode(" and "));
    const shareLink = document.createElement("a");
    shareLink.href = "#reach";
    shareLink.textContent = "share the petition";
    li.appendChild(shareLink);
    li.appendChild(document.createTextNode(" so the list can open with a real chorus."));
    sigList.appendChild(li);
  }

  function renderRecent(recent, count, namesVisible) {
    if (!sigList || !Array.isArray(recent)) return;
    const total = typeof count === "number" ? count : parseInt(sigList.getAttribute("data-count") || "0", 10) || 0;
    const visible =
      typeof namesVisible === "boolean" ? namesVisible : total >= namesThreshold();
    sigList.setAttribute("data-count", String(total));
    sigList.setAttribute("data-names-visible", visible ? "1" : "0");
    updateVoicesLede(total, visible);
    sigList.innerHTML = "";
    if (!visible) {
      renderHeldNamesState();
      return;
    }
    if (recent.length === 0) {
      const li = document.createElement("li");
      li.className = "sig-list__empty";
      li.id = "sig-empty";
      li.appendChild(document.createTextNode("No names yet. "));
      const link = document.createElement("a");
      link.href = "#sign";
      link.textContent = "Sign with your Forever character";
      li.appendChild(link);
      li.appendChild(document.createTextNode(" and open the list."));
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

  if (heroCount) {
    const target = parseInt(heroCount.getAttribute("data-count") || "0", 10) || 0;
    heroCount.textContent = "0";
    animateCount(heroCount, target);
  }
  if (signCount) {
    const target = parseInt(signCount.getAttribute("data-sign-count") || "0", 10) || 0;
    signCount.textContent = "0";
    animateCount(signCount, target);
  }

  async function copyText(value, button, idleLabel) {
    if (!button || !value) return;
    try {
      await navigator.clipboard.writeText(value);
    } catch (_) {
      const ta = document.createElement("textarea");
      ta.value = value;
      ta.setAttribute("readonly", "");
      ta.style.position = "absolute";
      ta.style.left = "-9999px";
      document.body.appendChild(ta);
      ta.select();
      document.execCommand("copy");
      document.body.removeChild(ta);
    }
    button.textContent = "Copied";
    setTimeout(() => {
      button.textContent = idleLabel;
    }, 1600);
  }

  if (copyBtn && shareUrl) {
    copyBtn.addEventListener("click", () => {
      copyText(shareUrl.value, copyBtn, "Copy link");
    });
  }

  if (copySuggestedBtn && suggestedCopy) {
    copySuggestedBtn.addEventListener("click", () => {
      copyText(suggestedCopy.textContent || "", copySuggestedBtn, "Copy text");
    });
  }

  if (characterInput) {
    characterInput.addEventListener("blur", () => {
      const err = form && form.querySelector('[data-error-for="character_name"]');
      if (!err) return;
      if (nameLooksAbusive(characterInput.value)) {
        characterInput.classList.add("invalid");
        err.textContent = "Use a Forever character name.";
        err.hidden = false;
      }
    });
  }

  // Hide sticky mobile CTA while the sign section is in view.
  const signSection = document.getElementById("sign");
  const mobileSign = document.querySelector(".mobile-sign");
  if (signSection && mobileSign && "IntersectionObserver" in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          document.body.classList.toggle("is-at-sign", entry.isIntersecting);
        });
      },
      { rootMargin: "-20% 0px -35% 0px", threshold: 0.05 }
    );
    io.observe(signSection);
  }

  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    clearFieldErrors();
    setStatus("", false);

    if (characterInput && nameLooksAbusive(characterInput.value)) {
      showFieldErrors({ character_name: "Use a Forever character name." });
      setStatus("Please fix the highlighted fields.", false);
      return;
    }

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
        setCounts(data.count);
      }
      if (data.recent) {
        renderRecent(
          data.recent,
          typeof data.count === "number" ? data.count : undefined,
          typeof data.names_visible === "boolean" ? data.names_visible : undefined
        );
      }
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
