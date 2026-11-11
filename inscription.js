document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("inscriptionForm");

    form.addEventListener("submit", function (e) {

        let nom = document.querySelector("[name='nom']").value.trim();
        let prenom = document.querySelector("[name='prenom']").value.trim();
        let email = document.querySelector("[name='email']").value.trim();
        let telephone = document.querySelector("[name='telephone']").value.trim();
        let niveau = document.querySelector("[name='niveau']").value;

        let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let nameRegex = /^[a-zA-Z\s]+$/;
        let phoneRegex = /^[0-9]{8,15}$/;

        // Nom
        if (nom === "" || !nameRegex.test(nom)) {
            alert("❌ le nom doit contenir des lettres ");
            e.preventDefault();
            return;
        }

        // Prénom
        if (prenom === "" || !nameRegex.test(prenom)) {
            alert("❌ le prenom doit contenir des lettres");
            e.preventDefault();
            return;
        }

        // Email
        if (!emailRegex.test(email)) {
            alert("❌ exmple@gmail.com");
            e.preventDefault();
            return;
        }

        // Téléphone
        if (!phoneRegex.test(telephone)) {
            alert("❌ le telephonr doit etre des chiffres");
            e.preventDefault();
            return;
        }

        // Niveau
        if (niveau === "") {
            alert("❌ Veuillez choisir un niveau");
            e.preventDefault();
            return;
        }

        alert("✅ Inscription validée !");
    });

});let currentStep = 1;

// Update summary when session changes
const sessionData = {
  "5":  { title:"Introduction Finance",       formation:"Finance",              date:"05 Avr. 2026", duree:"15h", salle:"SALLE A3",  type:"Présentiel", formateur:"Khalil Ahmed",    specialite:"Informatique" },
  "6":  { title:"Bases HTML & CSS",           formation:"Programmation Web",    date:"10 Avr. 2026", duree:"10h", salle:"SALLE A50", type:"Présentiel", formateur:"Yessine Ben Ahmed","specialite":"Informatique" },
  "7":  { title:"JavaScript Fondamentaux",    formation:"Programmation Web",    date:"20 Avr. 2026", duree:"10h", salle:"SALLE A50", type:"Présentiel", formateur:"Yessine Ben Ahmed","specialite":"Informatique" },
  "8":  { title:"Comptabilité Générale",      formation:"Finance",              date:"25 Avr. 2026", duree:"15h", salle:"SALLE A3",  type:"Présentiel", formateur:"Khalil Ahmed",    specialite:"Informatique" },
  "3":  { title:"CSS Avancé",                 formation:"Programmation Web",    date:"01 Mai 2026",  duree:"30h", salle:"—",         type:"Présentiel", formateur:"Yessine Ben Ahmed","specialite":"Informatique" },
  "4":  { title:"Finance Approfondie",        formation:"Finance",              date:"09 Mai 2026",  duree:"15h", salle:"—",         type:"Présentiel", formateur:"Khalil Ahmed",    specialite:"Informatique" }
};

document.getElementById('id_session').addEventListener('change', function() {
  const d = sessionData[this.value];
  if (!d) return;
  document.getElementById('sum-session-title').textContent = d.title;
  document.getElementById('sum-formation').textContent = d.formation;
  document.getElementById('sum-date').textContent = d.date;
  document.getElementById('sum-duree').textContent = d.duree;
  document.getElementById('sum-salle').textContent = d.salle;
  document.getElementById('sum-type').textContent = d.type;
  document.getElementById('sum-formateur').textContent = d.formateur;
  document.getElementById('sum-specialite').textContent = 'Spécialité : ' + d.specialite;
});

// Password strength
document.getElementById('password').addEventListener('input', function() {
  const v = this.value;
  setCheck('crit-len',   v.length >= 8);
  setCheck('crit-upper', /[A-Z]/.test(v));
  setCheck('crit-num',   /[0-9]/.test(v));
});
function setCheck(id, ok) {
  const el = document.getElementById(id);
  el.innerHTML = ok
    ? '<span style="color:var(--green)">✅</span> ' + el.textContent.replace(/^[^\s]+\s/,'')
    : '<span>⚪</span> ' + el.textContent.replace(/^[^\s]+\s/,'');
}

// Card preview
function updateCardPreview() {
  const name = document.getElementById('nom-titulaire').value.toUpperCase() || 'VOTRE NOM';
  const num  = document.getElementById('num-carte').value || '';
  document.getElementById('disp-name').textContent = name;
  document.getElementById('disp-num').textContent  = num || '•••• •••• •••• ••••';
}
function formatCardNum(input) {
  let v = input.value.replace(/\D/g,'').substring(0,16);
  input.value = v.replace(/(.{4})/g,'$1 ').trim();
}
function formatExpiry(input) {
  let v = input.value.replace(/\D/g,'').substring(0,4);
  if (v.length >= 3) v = v.slice(0,2) + '/' + v.slice(2);
  input.value = v;
}

// Navigation
function goToStep(n) {
  if (n > currentStep && !validateStep(currentStep)) return;
  document.getElementById('step' + currentStep).classList.remove('active');
  document.getElementById('step-ind-' + currentStep).classList.remove('active');
  document.getElementById('step-ind-' + currentStep).classList.add('done');
  document.getElementById('sc' + currentStep).textContent = '✓';

  if (n <= currentStep) {
    document.getElementById('step-ind-' + currentStep).classList.remove('done');
    document.getElementById('step-ind-' + currentStep).classList.remove('active');
    document.getElementById('sc' + currentStep).textContent = currentStep;
    document.getElementById('step-ind-' + n).classList.remove('done');
  }

  currentStep = n;
  document.getElementById('step' + currentStep).classList.add('active');
  document.getElementById('step-ind-' + currentStep).classList.add('active');
  document.getElementById('step-ind-' + currentStep).classList.remove('done');
  document.getElementById('sc' + currentStep).textContent = currentStep;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateStep(step) {
  let ok = true;
  if (step === 1) {
    ok = reqField('nom','err-nom') & reqField('prenom','err-prenom') & reqEmail() & reqField('tel','err-tel');
  } else if (step === 2) {
    ok = reqPassword() & reqSession() & reqCGU();
  } else if (step === 3) {
    ok = reqCIN() & reqField('nom-titulaire','err-titulaire') & reqCard() & reqExpiry() & reqPIN();
  }
  return ok;
}

function reqField(id, errId) {
  const v = document.getElementById(id).value.trim();
  const err = document.getElementById(errId);
  if (!v) { err.classList.add('show'); document.getElementById(id).classList.add('error'); return false; }
  err.classList.remove('show'); document.getElementById(id).classList.remove('error'); document.getElementById(id).classList.add('valid');
  return true;
}
function reqEmail() {
  const v = document.getElementById('email').value.trim();
  const err = document.getElementById('err-email');
  const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  if (!ok) { err.classList.add('show'); document.getElementById('email').classList.add('error'); return false; }
  err.classList.remove('show'); document.getElementById('email').classList.add('valid');
  return true;
}
function reqPassword() {
  const v = document.getElementById('password').value;
  const v2 = document.getElementById('password-confirm').value;
  let ok = true;

  // Vérifier la longueur du mot de passe
  if (v.length < 8) {
    document.getElementById('err-password').classList.add('show');
    ok = false;
  } else {
    document.getElementById('err-password').classList.remove('show');
  }

  // Vérifier la confirmation du mot de passe
  if (v !== v2) {
    document.getElementById('err-password-confirm').classList.add('show');
    ok = false;
  } else {
    document.getElementById('err-password-confirm').classList.remove('show');
  }

  return ok;
}
function reqSession() {
  const v = document.getElementById('id_session').value;
  if (!v) { document.getElementById('err-session').classList.add('show'); return false; }
  document.getElementById('err-session').classList.remove('show'); return true;
}
function reqCGU() {
  if (!document.getElementById('cgu').checked) {
    alert('Veuillez accepter les conditions générales d\'utilisation.'); return false;
  }
  return true;
}
function reqCIN() {
  const v = document.getElementById('cin').value.trim();
  const ok = /^\d{8}$/.test(v);
  if (!ok) { document.getElementById('err-cin').classList.add('show'); return false; }
  document.getElementById('err-cin').classList.remove('show'); return true;
}
function reqCard() {
  const v = document.getElementById('num-carte').value.replace(/\s/g,'');
  const ok = /^\d{16}$/.test(v);
  if (!ok) { document.getElementById('err-carte').classList.add('show'); return false; }
  document.getElementById('err-carte').classList.remove('show'); return true;
}
function reqExpiry() {
  const v = document.getElementById('expiry').value;
  const ok = /^(0[1-9]|1[0-2])\/\d{2}$/.test(v);
  if (!ok) { document.getElementById('err-expiry').classList.add('show'); return false; }
  document.getElementById('err-expiry').classList.remove('show'); return true;
}
function reqPIN() {
  const v = document.getElementById('code-pin').value;
  const ok = /^\d{4}$/.test(v);
  if (!ok) { document.getElementById('err-pin').classList.add('show'); return false; }
  document.getElementById('err-pin').classList.remove('show'); return true;
}

function submitForm() {
  if (!validateStep(3)) return;
  // Hide step 3, show success
  document.getElementById('step3').classList.remove('active');
  document.getElementById('step-ind-3').classList.remove('active');
  document.getElementById('step-ind-3').classList.add('done');
  document.getElementById('sc3').textContent = '✓';
  // Generate ref
  const ref = 'INS-2026-' + String(Math.floor(Math.random()*900)+100).padStart(4,'0');
  document.getElementById('ref-code').textContent = ref;
  document.getElementById('step-success').style.display = 'block';
  document.getElementById('step-success').querySelector('.success-screen').classList.add('show');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}