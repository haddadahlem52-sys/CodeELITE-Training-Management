
const titles = {
    accueil: 'Accueil',
    presence: 'Suivi des présences',
    participants: 'Liste des participants',
    sessions: 'Mes sessions'
};

function showPage(id, el) {
    // Cacher toutes les pages
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    // Désactiver tous les liens du menu
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    // Afficher la page choisie
    document.getElementById('page-' + id).classList.add('active');
    // Activer le lien cliqué
    el.classList.add('active');
    // Mettre à jour le titre
    document.getElementById('page-title').textContent = titles[id];
}

