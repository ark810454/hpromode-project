document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('[data-site-nav]');
    const setNavState = () => {
        if (!nav) {
            return;
        }

        nav.classList.toggle('is-scrolled', window.scrollY > 24);
    };

    setNavState();
    window.addEventListener('scroll', setNavState, { passive: true });

    const revealItems = document.querySelectorAll('.reveal-up');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.16 });

    revealItems.forEach((item) => observer.observe(item));

    const galleryMain = document.querySelector('[data-gallery-main]');
    const galleryThumbs = document.querySelectorAll('[data-gallery-thumb]');
    galleryThumbs.forEach((button) => {
        button.addEventListener('click', () => {
            if (!galleryMain) {
                return;
            }

            galleryMain.src = button.dataset.image || galleryMain.src;
            galleryMain.alt = button.dataset.alt || galleryMain.alt;
            galleryThumbs.forEach((thumb) => thumb.classList.remove('is-active'));
            button.classList.add('is-active');
        });
    });

    const newsletterForms = document.querySelectorAll('[data-newsletter-form]');
    newsletterForms.forEach((newsletterForm) => {
        newsletterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const button = newsletterForm.querySelector('button[type="submit"]');
            if (button) {
                button.textContent = 'Merci pour votre inscription';
                button.disabled = true;
            }
        });
    });

    const paymentMethod = document.querySelector('[data-payment-method]');
    const cardFields = document.querySelector('[data-card-fields]');

    const toggleCardFields = () => {
        if (!paymentMethod || !cardFields) {
            return;
        }

        const isCard = (paymentMethod.value || '').toLowerCase().includes('carte');
        cardFields.classList.toggle('d-none', !isCard);
    };

    if (paymentMethod) {
        paymentMethod.addEventListener('change', toggleCardFields);
        toggleCardFields();
    }
});
