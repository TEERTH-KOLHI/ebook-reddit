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
});
