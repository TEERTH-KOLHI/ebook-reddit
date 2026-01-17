document.addEventListener('DOMContentLoaded', () => {
    // Bank Proof Zoom Effect (Amazon Style Lens)
    const proofCards = document.querySelectorAll('.proof-card');
    const resultDiv = document.getElementById('zoom-result');

    if (!resultDiv) {
        console.warn('Zoom result container not found');
        return;
    }

    proofCards.forEach(card => {
        const img = card.querySelector('img');

        card.addEventListener('mouseenter', () => {
            // Set background image
            resultDiv.style.backgroundImage = `url('${img.src}')`;
            resultDiv.style.visibility = 'visible';
            resultDiv.style.opacity = '1';
            // Scale up: standard zoom is roughly 2x - 2.5x
            resultDiv.style.backgroundSize = `${img.width * 2.5}px ${img.height * 2.5}px`;
        });

        card.addEventListener('mouseleave', () => {
            resultDiv.style.visibility = 'hidden';
            resultDiv.style.opacity = '0';
        });

        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            // Cursor relative to image
            let x = e.clientX - rect.left;
            let y = e.clientY - rect.top;

            // Dashboard relative coords for the flyout position
            const dashRect = document.querySelector('.dashboard-interface').getBoundingClientRect();
            const dashX = e.clientX - dashRect.left;
            const dashY = e.clientY - dashRect.top;

            // Position the magnifying glass (lens) near the cursor
            // Offset logic:
            // If x is > 50% width, show on left?
            // Simple approach: Always to the right + offset.
            resultDiv.style.left = `${dashX + 30}px`;
            resultDiv.style.top = `${dashY - 150}px`; // Move up/center vertically relative to cursor

            // Calculate background position to center the content
            // Formula: (LensCenter) - (CursorRelativeToImage * ZoomFactor)
            const zoomFactor = 2.5;
            const lensHalfW = resultDiv.offsetWidth / 2;
            const lensHalfH = resultDiv.offsetHeight / 2;

            const bgX = lensHalfW - (x * zoomFactor);
            const bgY = lensHalfH - (y * zoomFactor);

            resultDiv.style.backgroundPosition = `${bgX}px ${bgY}px`;
        });
    });
    // FAQ Logic
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', () => {
            // Close other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            // Toggle current
            item.classList.toggle('active');
        });
    });

    // Sneak Peek Carousel Logic
    const spCards = document.querySelectorAll('.sp-page-card');
    const prevBtn = document.querySelector('.sp-nav-btn.prev-btn');
    const nextBtn = document.querySelector('.sp-nav-btn.next-btn');
    let currentIndex = 0; // Start at first item (data-index 0)

    function updateCarousel() {
        spCards.forEach((card, index) => {
            card.className = 'sp-page-card'; // Reset classes
            card.style.opacity = '';
            card.style.transform = '';
            card.style.zIndex = '';

            // Calc indices for wrapping
            let prevIndex = currentIndex - 1;
            if (prevIndex < 0) prevIndex = spCards.length - 1;

            let nextIndex = currentIndex + 1;
            if (nextIndex >= spCards.length) nextIndex = 0;

            if (index === currentIndex) {
                card.classList.add('active');
            } else if (index === prevIndex) {
                card.classList.add('prev');
            } else if (index === nextIndex) {
                card.classList.add('next');
            } else {
                // If out of view
                card.style.opacity = '0';
            }
        });
    }

    if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', () => {
            // Infinite loop: if at 0, go to length-1, else decrement
            if (currentIndex > 0) {
                currentIndex--;
            } else {
                currentIndex = spCards.length - 1;
            }
            updateCarousel();
        });

        nextBtn.addEventListener('click', () => {
            // Infinite loop: if at length-1, go to 0, else increment
            if (currentIndex < spCards.length - 1) {
                currentIndex++;
            } else {
                currentIndex = 0;
            }
            updateCarousel();
        });
    }

});
