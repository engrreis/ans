// js/cadastro.js

(function () {
  const $ = (id) => document.getElementById(id);

  // -----------------------
  // Máscaras
  // -----------------------
  const onlyDigits = (s) => (s || "").toString().replace(/\D+/g, "");

  function maskCPF(value) {
    const d = onlyDigits(value).slice(0, 11);
    let out = d;
    if (d.length > 9) out = d.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2}).*$/, "$1.$2.$3-$4");
    else if (d.length > 6) out = d.replace(/^(\d{3})(\d{3})(\d{0,3}).*$/, "$1.$2.$3");
    else if (d.length > 3) out = d.replace(/^(\d{3})(\d{0,3}).*$/, "$1.$2");
    return out;
  }

  function maskPhone(value) {
    const d = onlyDigits(value).slice(0, 11);
    if (d.length <= 10) {
      // (11) 9999-9999
      return d
        .replace(/^(\d{0,2})/, "($1")
        .replace(/^\((\d{2})(\d{0,4})/, "($1) $2")
        .replace(/^\((\d{2})\)\s(\d{4})(\d{0,4}).*$/, "($1) $2-$3")
        .replace(/\s-$/, "");
    }
    // (11) 99999-9999
    return d
      .replace(/^(\d{0,2})/, "($1")
      .replace(/^\((\d{2})(\d{0,5})/, "($1) $2")
      .replace(/^\((\d{2})\)\s(\d{5})(\d{0,4}).*$/, "($1) $2-$3")
      .replace(/\s-$/, "");
  }

  function maskCEP(value) {
    const d = onlyDigits(value).slice(0, 8);
    if (d.length <= 5) return d;
    return d.replace(/^(\d{5})(\d{0,3}).*$/, "$1-$2");
  }

  // RG: formata de trás pra frente
  // - último dígito = dígito verificador
  // - pontos a cada 3 dígitos no restante
  // - o primeiro grupo pode ter 1 a 3 dígitos
  function formatRGFromDigits(digits) {
    const d = onlyDigits(digits);
    if (!d) return "";
    if (d.length === 1) return d;

    const dv = d.slice(-1);
    let base = d.slice(0, -1);

    const groups = [];
    while (base.length > 3) {
      groups.unshift(base.slice(-3));
      base = base.slice(0, -3);
    }
    if (base.length) groups.unshift(base);

    return groups.join(".") + "-" + dv;
  }

  // -----------------------
  // CEP via ViaCEP
  // -----------------------
  async function lookupCEP(cepDigits) {
    const status = $("cepStatus");
    if (!status) return;

    if (cepDigits.length !== 8) {
      status.textContent = "";
      return;
    }

    status.textContent = "Buscando CEP...";
    try {
      const url = `https://viacep.com.br/ws/${cepDigits}/json/`;
      const res = await fetch(url, { method: "GET" });
      const data = await res.json();

      if (data.erro) {
        status.textContent = "CEP não encontrado.";
        return;
      }

      // Preenche sem tocar no complemento (importante)
      if ($("rua")) $("rua").value = data.logradouro || $("rua").value;
      if ($("bairro")) $("bairro").value = data.bairro || $("bairro").value;
      if ($("cidade")) $("cidade").value = data.localidade || $("cidade").value;
      if ($("uf")) $("uf").value = (data.uf || $("uf").value || "").toUpperCase();

      status.textContent = "CEP confirmado.";
    } catch (e) {
      status.textContent = "Não foi possível buscar o CEP agora.";
    }
  }

  // -----------------------
  // CIN toggle (RG = CPF)
  // -----------------------
  function applyCINState() {
    const cin = $("possui_cin");
    const rgWrap = $("rgWrap");
    const rg = $("rg");
    const cpf = $("cpf");
    if (!cin || !rgWrap || !rg || !cpf) return;

    if (cin.checked) {
      rgWrap.style.display = "none";
      rg.value = ""; // não exibe
    } else {
      rgWrap.style.display = "";
    }
  }

  // -----------------------
  // Procurador toggle
  // -----------------------
  function applyProcuradorState() {
    const sou = $("sou_procurador");
    const block = $("representadoBlock");
    if (!sou || !block) return;

    block.style.display = sou.checked ? "" : "none";

    // required dinâmico (somente quando procurador)
    const rn = $("representado_nome");
    const re = $("representado_email");
    if (rn) rn.required = !!sou.checked;
    if (re) re.required = !!sou.checked;
  }

  // -----------------------
  // Help modal
  // -----------------------
  function modalOpen() {
    const m = $("helpModal");
    if (!m) return;
    m.classList.add("is-open");
    m.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }
  function modalClose() {
    const m = $("helpModal");
    if (!m) return;
    m.classList.remove("is-open");
    m.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  // -----------------------
  // Bindings
  // -----------------------
  document.addEventListener("DOMContentLoaded", () => {
    const cpf = $("cpf");
    const tel = $("telefone");
    const cep = $("cep");
    const rg = $("rg");

    if (cpf) {
      cpf.addEventListener("input", () => {
        cpf.value = maskCPF(cpf.value);
        // se CIN estiver marcado, RG (implícito) é CPF — nada a fazer visualmente
      });
    }

    if (tel) {
      tel.addEventListener("input", () => {
        tel.value = maskPhone(tel.value);
      });
    }

    if (cep) {
      cep.addEventListener("input", () => {
        cep.value = maskCEP(cep.value);
        const d = onlyDigits(cep.value);
        if (d.length === 8) lookupCEP(d);
      });
      cep.addEventListener("blur", () => {
        const d = onlyDigits(cep.value);
        if (d.length === 8) lookupCEP(d);
      });
    }

    if (rg) {
      rg.addEventListener("input", () => {
        // permite digitar números, formata no input
        const d = onlyDigits(rg.value).slice(0, 14); // RG pode variar; deixa folga
        rg.value = formatRGFromDigits(d);
      });
      rg.addEventListener("blur", () => {
        const d = onlyDigits(rg.value);
        rg.value = formatRGFromDigits(d);
      });
    }

    const cin = $("possui_cin");
    if (cin) {
      cin.addEventListener("change", () => applyCINState());
      applyCINState();
    }

    const sou = $("sou_procurador");
    if (sou) {
      sou.addEventListener("change", () => applyProcuradorState());
      applyProcuradorState();
    }

    const helpBtn = $("helpProcuracaoBtn");
    if (helpBtn) helpBtn.addEventListener("click", modalOpen);

    const helpModal = $("helpModal");
    if (helpModal) {
      helpModal.addEventListener("click", (e) => {
        const t = e.target;
        if (t && t.getAttribute && t.getAttribute("data-close") === "1") modalClose();
      });
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") modalClose();
      });
    }

    // UF: força uppercase
    const uf = $("uf");
    if (uf) uf.addEventListener("input", () => (uf.value = (uf.value || "").toUpperCase().slice(0, 2)));
  });
})();
