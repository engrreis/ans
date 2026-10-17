(function(){
  const buttons = document.querySelectorAll('[data-slot]');
  const form = document.getElementById('agendaForm');
  const horaEscolhida = document.getElementById('horaEscolhida');
  const horaVisual = document.getElementById('horaVisual');
  const cancelar = document.getElementById('cancelar');
  const telefone = document.getElementById('telefone');
  const dataSel = "<?= h($selectedDate) ?>";
  function showForm(hh){
    horaEscolhida.value = hh;
    horaVisual.value = (dataSel.split('-').reverse().join('/')) + ' às ' + hh;
    form.style.display = 'block';
    form.scrollIntoView({behavior:'smooth', block:'start'});
  }
  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      if (btn.disabled) return;
      showForm(btn.getAttribute('data-slot'));
    });
  });
  cancelar && cancelar.addEventListener('click', () => {
    form.style.display = 'none';
    horaEscolhida.value = '';
    horaVisual.value = '';
  });
  // máscara simples: (16) 99999-9999
  function maskPhone(v){
    v = (v || '').replace(/\D/g,'').slice(0,11);
    if (v.length <= 10){
      // (##) ####-####
      return v.replace(/^(\d{0,2})(\d{0,4})(\d{0,4}).*/, function(_,a,b,c){
        let out = '';
        if (a) out += '('+a;
        if (a.length===2) out += ') ';
        if (b) out += b;
        if (b.length===4) out += '-';
        if (c) out += c;
        return out;
      });
    } else {
      // (##) #####-####
      return v.replace(/^(\d{0,2})(\d{0,5})(\d{0,4}).*/, function(_,a,b,c){
        let out = '';
        if (a) out += '('+a;
        if (a.length===2) out += ') ';
        if (b) out += b;
        if (b.length===5) out += '-';
        if (c) out += c;
        return out;
      });
    }
  }
  if (telefone){
    telefone.addEventListener('input', () => telefone.value = maskPhone(telefone.value));
    telefone.addEventListener('blur', () => telefone.value = maskPhone(telefone.value));
  }
})();