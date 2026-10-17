(() => {
  const header = document.getElementById("siteHeader");
  const nav = document.getElementById("siteNav");
  const toggle = document.getElementById("menuToggle");
  const backdrop = document.getElementById("navBackdrop");

  if (!header) return;

  const TOP_THRESHOLD = 10;

  function closeNav() {
    nav?.classList.remove("is-open");
    toggle?.setAttribute("aria-expanded", "false");
    if (backdrop) backdrop.hidden = true;
    document.documentElement.style.overflow = "";
  }

  function openNav() {
    nav?.classList.add("is-open");
    toggle?.setAttribute("aria-expanded", "true");
    if (backdrop) backdrop.hidden = false;
    document.documentElement.style.overflow = "hidden";
  }

  function setHeaderVisibility() {
    const y = window.scrollY || 0;

    if (y <= TOP_THRESHOLD) {
      header.classList.remove("is-hidden");
    } else {
      header.classList.add("is-hidden");
      closeNav();
    }
  }

  toggle?.addEventListener("click", () => {
    const isOpen = nav?.classList.contains("is-open");
    if (isOpen) closeNav();
    else openNav();
  });

  backdrop?.addEventListener("click", closeNav);

  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeNav();
  });

  nav?.addEventListener("click", (e) => {
    const a = e.target.closest("a");
    if (a && nav.classList.contains("is-open")) closeNav();
  });

  window.addEventListener("scroll", setHeaderVisibility, { passive: true });
  window.addEventListener("load", setHeaderVisibility);
  setHeaderVisibility();
})();
