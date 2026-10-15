<?php
session_start();
$page_title = "Inscription";

// Récupérer les anciennes valeurs en cas d'erreur
$old = $_SESSION['old_input'] ?? [];
$flash_error = $_SESSION['flash_error'] ?? null;
$flash_success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['old_input'], $_SESSION['flash_error'], $_SESSION['flash_success']);

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    header('Location: profil.php');
    exit;
}

include 'includes/header.php'; 
?>

<section class="auth-page">
    <div class="auth-container">
        
        <!-- EN-TÊTE -->
        <div class="auth-header">
            <h1>Créer votre compte</h1>
            <p>Rejoignez-nous et commencez votre formation</p>
        </div>

        <!-- FORMULAIRE -->
        <form class="auth-form" id="registerForm" method="POST" action="traitement_inscription.php" novalidate>
            
            

            <!-- NOM + PRÉNOM -->
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom <span class="required">*</span></label>
                    <div class="input-wrapper">
                        
                        <input type="text" id="nom" name="nom" placeholder="Votre nom" required value="<?= htmlspecialchars($old['nom'] ?? '') ?>">
                    </div>
                    <span class="error-message" id="error-nom">Veuillez entrer votre nom</span>
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom <span class="required">*</span></label>
                    <div class="input-wrapper">
                        
                        <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" required value="<?= htmlspecialchars($old['prenom'] ?? '') ?>">
                    </div>
                    <span class="error-message" id="error-prenom">Veuillez entrer votre prénom</span>
                </div>
            </div>

            <!-- EMAIL -->
            <div class="form-group">
                <label for="email">Adresse email <span class="required">*</span></label>
                <div class="input-wrapper">
                    <span class="icon"></span>
                    <input type="email" id="email" name="email" placeholder="exemple@email.com" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                </div>
                <span class="error-message" id="error-email">Veuillez entrer un email valide</span>
            </div>

            <!-- TÉLÉPHONE -->
            <div class="form-group">
                <label for="tel">Numéro de téléphone <span class="required">*</span></label>
                <div class="input-wrapper">
                    <span class="icon"></span>
                    <input type="tel" id="tel" name="tel" placeholder="+229 00 00 00 00" required value="<?= htmlspecialchars($old['tel'] ?? '') ?>">
                </div>
                <span class="error-message" id="error-tel">Numéro de téléphone invalide</span>
            </div>

            <!-- TYPE DE PERMIS (NOUVEAU) -->
            <div class="form-group">
                <label for="type_permis">Type de permis souhaité <span class="required">*</span></label>
                <div class="input-wrapper">
                    <span class="icon"></span>
                    <select id="type_permis" name="type_permis" required value="<?= htmlspecialchars($old['type_permis'] ?? '') ?>">
                        <option value="" disabled selected>Sélectionnez une catégorie</option>
                        <option value="code"> Code de la route uniquement</option>
                        <option value="B"> Permis Voiture (Catégorie B)</option>
                        <option value="A1"> Permis Moto A1 (125 cm³)</option>
                        <option value="A2"> Permis Moto A2 (intermédiaire)</option>
                        <option value="A"> Permis Moto A (gros cube)</option>
                        <option value="C"> Permis Poids Lourd (Catégorie C)</option>
                        <option value="D"> Permis Bus / Transport en commun (Catégorie D)</option>
                        <option value="perfectionnement"> Perfectionnement (reprise de confiance)</option>
                    </select>
                </div>
                <span class="error-message" id="error-type_permis">Veuillez sélectionner un type de permis</span>
                
                <!-- Info box dynamique -->
                <div class="info-box" id="info-permis"></div>
            </div>

            <!-- MOT DE PASSE -->
            <div class="form-group">
                <label for="mdp">Mot de passe <span class="required">*</span></label>
                <div class="input-wrapper">
                    <span class="icon"></span>
                    <input type="password" id="mdp" name="mdp" placeholder="Minimum 8 caractères" required minlength="8" value="<?= htmlspecialchars($old['mdp'] ?? '') ?>">
                    <button type="button" class="toggle-password" data-target="mdp" aria-label="Afficher le mot de passe"  onclick="let i=document.getElementById('mdp');i.type=i.type==='password'?'text':'password'"><img src="img/bx-show.svg" alt=""></button>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="strength-bar"></div>
                </div>
                <div class="password-strength-text" id="strength-text"></div>
                <span class="error-message" id="error-mdp">Le mot de passe doit contenir au moins 8 caractères</span>
            </div>

            <!-- CONFIRMATION MOT DE PASSE -->
            <div class="form-group">
                <label for="mdp_confirm">Confirmer le mot de passe <span class="required">*</span></label>
                <div class="input-wrapper">
                    <span class="icon"></span>
                    <input type="password" id="mdp_confirm" name="mdp_confirm" placeholder="Retapez votre mot de passe" required value="<?= htmlspecialchars($old['mdp_comfirm'] ?? '') ?>">
                    <button type="button" class="toggle-password" data-target="mdp_confirm" aria-label="Afficher le mot de passe" onclick="let i=document.getElementById('mdp_confirm');i.type=i.type==='password'?'text':'password'"><img src="img/bx-show.svg" alt=""></button>
                </div>
                <span class="error-message" id="error-mdp_confirm">Les mots de passe ne correspondent pas</span>
            </div>

            <!-- CONDITIONS -->
            <div class="checkbox-group">
                <input type="checkbox" id="conditions" name="conditions" required value="<?= htmlspecialchars($old['conditions'] ?? '') ?>">
                <label for="conditions">
                    J'accepte les <a href="#">conditions d'utilisation</a> et la <a href="#">politique de confidentialité</a>
                </label>
            </div>
            <span class="error-message" id="error-conditions" style="margin-top:-12px;margin-bottom:10px;">Vous devez accepter les conditions</span>

            <!-- BOUTON -->
            <button type="submit" class="btn-submit" id="submitBtn">
                Créer mon compte
            </button>
        </form>

        <!-- LIEN CONNEXION -->
        <div class="auth-footer">
            Vous avez déjà un compte ? <a href="connexion.php">Se connecter</a>
        </div>
    </div>
</section>

<script>
// ====== Toggle password visibility ======
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const target = document.getElementById(btn.dataset.target);
        const isPassword = target.type === 'password';
        target.type = isPassword ? 'text' : 'password';

        btn. = isPassword ? '🙈' : '👁️';
    });
});

// ====== Password strength ======
const mdp = document.getElementById('mdp');
const strengthBar = document.getElementById('strength-bar');
const strengthText = document.getElementById('strength-text');

mdp.addEventListener('input', () => {
    const value = mdp.value;
    let score = 0;
    
    if (value.length >= 8) score++;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
    if (/\d/.test(value)) score++;
    if (/[^a-zA-Z0-9]/.test(value)) score++;
    
    strengthBar.className = 'password-strength-bar';
    
    if (value.length === 0) {
        strengthText.textContent = '';
        return;
    }
    
    if (score <= 1) {
        strengthBar.classList.add('strength-weak');
        strengthText.textContent = ' Faible';
        strengthText.style.color = '#dc2626';
    } else if (score <= 2) {
        strengthBar.classList.add('strength-medium');
        strengthText.textContent = ' Moyen';
        strengthText.style.color = '#f59e0b';
    } else {
        strengthBar.classList.add('strength-strong');
        strengthText.textContent = 'Fort';
        strengthText.style.color = '#16a34a';
    }
});

// ====== Info box dynamique pour le type de permis ======
const typePermis = document.getElementById('type_permis');
const infoBox = document.getElementById('info-permis');

const permisInfos = {
    'code': '<strong> Code de la route</strong><br>Formation théorique uniquement. Idéal pour préparer l\'examen du code avant la conduite.',
    'B': '<strong> Permis Voiture (B)</strong><br>Formation complète : code + minimum 20h de conduite. Durée : 1 à 3 mois.',
    'A1': '<strong> Permis Moto A1</strong><br>Accessible dès 16 ans. Motocyclettes légères (125 cm³, 11 kW max).',
    'A2': '<strong> Permis Moto A2</strong><br>Accessible dès 18 ans. Motocyclettes de puissance intermédiaire (35 kW max).',
    'A': '<strong> Permis Moto A</strong><br>Accessible après 2 ans de permis A2. Toutes les motos sans limitation.',
    'C': '<strong> Permis Poids Lourd (C)</strong><br>Formation professionnelle. Véhicules de plus de 3,5 tonnes. Durée : 2 à 4 mois.',
    'D': '<strong> Permis Bus (D)</strong><br>Transport en commun. Véhicules de plus de 9 places. Durée : 2 à 4 mois.',
    'perfectionnement': '<strong> Perfectionnement</strong><br>Vous avez déjà le permis ? Reprenez confiance avec nos stages personnalisés.'
};

typePermis.addEventListener('change', () => {
    const value = typePermis.value;
    if (permisInfos[value]) {
        infoBox.innerHTML = permisInfos[value];
        infoBox.classList.add('show');
    } else {
        infoBox.classList.remove('show');
    }
});

// ====== Validation du formulaire ======
const form = document.getElementById('registerForm');

function showError(fieldId, show = true) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById('error-' + fieldId);
    if (show) {
        field.classList.add('error');
        field.classList.remove('success');
        if (error) error.classList.add('show');
    } else {
        field.classList.remove('error');
        field.classList.add('success');
        if (error) error.classList.remove('show');
    }
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePhone(phone) {
    const cleaned = phone.replace(/\s|-|\./g, '');
    return /^\+?[0-9]{8,15}$/.test(cleaned);
}

form.addEventListener('submit', (e) => {
    let isValid = true;
    
    // Nom
    if (document.getElementById('nom').value.trim().length < 2) {
        showError('nom', true);
        isValid = false;
    } else {
        showError('nom', false);
    }
    
    // Prénom
    if (document.getElementById('prenom').value.trim().length < 2) {
        showError('prenom', true);
        isValid = false;
    } else {
        showError('prenom', false);
    }
    
    // Email
    if (!validateEmail(document.getElementById('email').value)) {
        showError('email', true);
        isValid = false;
    } else {
        showError('email', false);
    }
    
    // Téléphone
    if (!validatePhone(document.getElementById('tel').value)) {
        showError('tel', true);
        isValid = false;
    } else {
        showError('tel', false);
    }
    
    // Type de permis
    if (!document.getElementById('type_permis').value) {
        showError('type_permis', true);
        isValid = false;
    } else {
        showError('type_permis', false);
    }
    
    // Mot de passe
    if (document.getElementById('mdp').value.length < 8) {
        showError('mdp', true);
        isValid = false;
    } else {
        showError('mdp', false);
    }
    
    // Confirmation
    const mdpVal = document.getElementById('mdp').value;
    const confirmVal = document.getElementById('mdp_confirm').value;
    if (mdpVal !== confirmVal || confirmVal.length === 0) {
        showError('mdp_confirm', true);
        isValid = false;
    } else {
        showError('mdp_confirm', false);
    }
    
    // Conditions
    if (!document.getElementById('conditions').checked) {
        document.getElementById('error-conditions').classList.add('show');
        isValid = false;
    } else {
        document.getElementById('error-conditions').classList.remove('show');
    }
    
    if (!isValid) {
        e.preventDefault();
        // Scroll vers la première erreur
        const firstError = document.querySelector('.error, .error-message.show');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    // Si valide, le formulaire s'envoie normalement vers traitement_inscription.php
});

// Validation en temps réel (au blur)
['nom', 'prenom', 'email', 'tel', 'type_permis', 'mdp', 'mdp_confirm'].forEach(id => {
    const field = document.getElementById(id);
    if (field) {
        field.addEventListener('blur', () => {
            // Déclenche la validation individuelle si l'utilisateur a déjà interagi
            if (field.value) {
                // On relance la validation globale silencieusement
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>