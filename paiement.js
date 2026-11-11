function payer() {

    let type = document.getElementById("type").value;
    let cin = document.getElementById("cin").value.trim();
    let nom = document.getElementById("nom").value.trim();
    let carte = document.getElementById("carte").value.trim();
    let cvv = document.getElementById("cvv").value.trim();

    // TYPE
    if (type === "") {
        alert("❌ Choisir un type de carte");
        return;
    }

    // CIN
    if (cin === "") {
        alert("❌ CIN obligatoire");
        return;
    }
    if (!/^[0-9]{8}$/.test(cin)) {
        alert("❌ CIN doit contenir exactement 8 chiffres");
        return;
    }

    // NOM
    if (nom === "") {
        alert("❌ Nom obligatoire");
        return;
    }
    if (!/^[a-zA-Z\s]+$/.test(nom)) {
        alert("❌ Nom invalide (lettres seulement)");
        return;
    }

    // CARTE
    if (carte === "") {
        alert("❌ Numéro de carte obligatoire");
        return;
    }
    if (!/^[0-9]{16}$/.test(carte)) {
        alert("❌ Numéro de carte invalide (16 chiffres)");
        return;
    }

    // CVV
    if (cvv === "") {
        alert("❌ CVV obligatoire");
        return;
    }
    if (!/^[0-9]{3,4}$/.test(cvv)) {
        alert("❌ CVV invalide");
        return;
    }

    // récupérer ID depuis URL
    const params = new URLSearchParams(window.location.search);
    let inscription_id = params.get("inscription_id");

    fetch("paiement.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            inscription_id: inscription_id,
            type: type,
            cin: cin,
            nom: nom,
            carte: carte,
            cvv: cvv
        })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message || "✅ Paiement traité !");
    })
    .catch(() => {
        alert("❌ Erreur serveur");
    });
}