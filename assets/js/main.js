// ====== Effet typing ======
const words = ['unique', 'sérieuse', 'moderne', 'efficace', 'accessible'];
const dynamicWord = document.getElementById('dynamicWord');
let wordIndex = 0;
let charIndex = 0;
let isDeleting = false;
let typingSpeed = 120;

function typeEffect() {
    if (!dynamicWord) return;
    const currentWord = words[wordIndex];

    if (isDeleting) {
        dynamicWord.textContent = currentWord.substring(0, charIndex - 1);
        charIndex--;
        typingSpeed = 60;
    } else {
        dynamicWord.textContent = currentWord.substring(0, charIndex + 1);
        charIndex++;
        typingSpeed = 120;
    }

    if (!isDeleting && charIndex === currentWord.length) {
        isDeleting = true;
        typingSpeed = 1800;
    } else if (isDeleting && charIndex === 0) {
        isDeleting = false;
        wordIndex = (wordIndex + 1) % words.length;
        typingSpeed = 400;
    }

    setTimeout(typeEffect, typingSpeed);
}

// ====== Menu burger (amélioré) ======
const burger = document.getElementById('burger');
const navMenu = document.getElementById('navMenu');
const header = document.getElementById('header');

if (burger && navMenu) {
    burger.addEventListener('click', () => {
        const isOpen = navMenu.classList.toggle('open');
        burger.classList.toggle('active');
        burger.setAttribute('aria-expanded', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
    });

    // Fermer le menu au clic sur un lien
    document.querySelectorAll('.nav-menu a').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('open');
            burger.classList.remove('active');
            burger.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        });
    });
}

// ====== Header shadow au scroll ======
if (header) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 10) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
}

// ====== Filtres blog ======
const filterBtns = document.querySelectorAll('.filter-btn');
const posts = document.querySelectorAll('.post');

filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const filter = btn.dataset.filter;
        posts.forEach(post => {
            const show = filter === 'all' || post.dataset.cat === filter;
            post.style.display = show ? '' : 'none';
        });
    });
});

// ====== Lancement ======
document.addEventListener('DOMContentLoaded', () => {
    typeEffect();
});
