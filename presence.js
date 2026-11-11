
// quand on change le statut d'un participant
function changerStatut(id, select) {
  // changer la couleur du select
  select.className = select.value;

  // montrer ou cacher le champ justification
  var justif = document.getElementById('justif_' + id);
  if (select.value === 'absent' || select.value === 'retard') {
    justif.classList.add('visible');
  } else {
    justif.classList.remove('visible');
    justif.querySelector('input').value = '';
  }
}
