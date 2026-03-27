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

    const productMediaForm = document.querySelector('[data-product-media-form]');

    if (productMediaForm) {
        const mainImageInput = productMediaForm.querySelector('[data-main-image-file]');
        const mainImagePreview = productMediaForm.querySelector('[data-main-image-preview]');
        const mainImageData = productMediaForm.querySelector('[data-main-image-data]');
        const mainImageFilename = productMediaForm.querySelector('[data-main-image-filename]');
        const mainImagePath = productMediaForm.querySelector('[data-main-image-path]');
        const scaleInput = productMediaForm.querySelector('[data-crop-scale]');
        const offsetXInput = productMediaForm.querySelector('[data-crop-offset-x]');
        const offsetYInput = productMediaForm.querySelector('[data-crop-offset-y]');
        const galleryInput = productMediaForm.querySelector('[data-gallery-file-input]');
        const galleryData = productMediaForm.querySelector('[data-gallery-images-data]');
        const galleryPreviewList = productMediaForm.querySelector('[data-gallery-preview-list]');
        const targetWidth = 1200;
        const targetHeight = 1500;
        const mainEditorState = {
            image: null,
            fileName: '',
        };

        const toCanvasImage = (file, callback) => {
            const reader = new FileReader();
            reader.onload = (event) => {
                const image = new Image();
                image.onload = () => callback(image, event.target?.result || '', file.name);
                image.src = event.target?.result || '';
            };
            reader.readAsDataURL(file);
        };

        const createCroppedDataUrl = (image, scale, offsetX, offsetY) => {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = targetWidth;
            canvas.height = targetHeight;

            if (!context) {
                return '';
            }

            const baseScale = Math.max(targetWidth / image.width, targetHeight / image.height);
            const zoom = baseScale * scale;
            const drawWidth = image.width * zoom;
            const drawHeight = image.height * zoom;
            const drawX = (targetWidth - drawWidth) / 2 + offsetX;
            const drawY = (targetHeight - drawHeight) / 2 + offsetY;

            context.fillStyle = '#f3eee8';
            context.fillRect(0, 0, targetWidth, targetHeight);
            context.drawImage(image, drawX, drawY, drawWidth, drawHeight);

            return canvas.toDataURL('image/jpeg', 0.9);
        };

        const renderMainImage = () => {
            if (!mainEditorState.image || !mainImagePreview || !mainImageData) {
                return;
            }

            const scale = parseFloat(scaleInput?.value || '1');
            const offsetX = parseInt(offsetXInput?.value || '0', 10);
            const offsetY = parseInt(offsetYInput?.value || '0', 10);
            const dataUrl = createCroppedDataUrl(mainEditorState.image, scale, offsetX, offsetY);

            if (!dataUrl) {
                return;
            }

            mainImagePreview.src = dataUrl;
            mainImageData.value = dataUrl;
            if (mainImagePath) {
                mainImagePath.value = `assets/images/uploads/${mainEditorState.fileName || 'main-image.jpg'}`;
            }
        };

        if (mainImageInput) {
            mainImageInput.addEventListener('change', () => {
                const file = mainImageInput.files?.[0];
                if (!file) {
                    return;
                }

                toCanvasImage(file, (image, _dataUrl, fileName) => {
                    mainEditorState.image = image;
                    mainEditorState.fileName = fileName;
                    if (mainImageFilename) {
                        mainImageFilename.value = fileName;
                    }

                    if (scaleInput) {
                        scaleInput.value = '1';
                    }
                    if (offsetXInput) {
                        offsetXInput.value = '0';
                    }
                    if (offsetYInput) {
                        offsetYInput.value = '0';
                    }

                    renderMainImage();
                });
            });
        }

        [scaleInput, offsetXInput, offsetYInput].forEach((input) => {
            if (!input) {
                return;
            }

            input.addEventListener('input', renderMainImage);
        });

        if (galleryInput && galleryPreviewList && galleryData) {
            galleryInput.addEventListener('change', () => {
                const files = Array.from(galleryInput.files || []);
                const preparedImages = [];
                const newPreviewCards = [];

                if (!files.length) {
                    galleryData.value = '';
                    return;
                }

                let processedCount = 0;

                files.forEach((file) => {
                    toCanvasImage(file, (image, _dataUrl, fileName) => {
                        const preparedData = createCroppedDataUrl(image, 1, 0, 0);
                        preparedImages.push({
                            name: fileName,
                            data: preparedData,
                        });

                        const card = document.createElement('div');
                        card.className = 'admin-gallery-card is-new';
                        card.innerHTML = `<img src="${preparedData}" alt="${fileName}"><span>Prêt à publier</span>`;
                        newPreviewCards.push(card);

                        processedCount += 1;

                        if (processedCount === files.length) {
                            galleryData.value = JSON.stringify(preparedImages);
                            newPreviewCards.forEach((cardElement) => galleryPreviewList.prepend(cardElement));
                        }
                    });
                });
            });
        }
    }
});
