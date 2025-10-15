// document.addEventListener('DOMContentLoaded', () => {
    const shareBtn = document.getElementById('share-btn');
    if (shareBtn) {
        shareBtn.addEventListener('click', () => {
            const pageUrl = window.location.href;
            navigator.clipboard.writeText(pageUrl)
                .then(() => showCopiedMessage('Link copied to clipboard!'))
                .catch(err => {
                    console.error('Failed to copy:', err);
                    showCopiedMessage('Failed to copy link');
                });
        });
    }

    function showCopiedMessage(message) {
            const msg = document.createElement('div');
            msg.textContent = message;
            msg.className = 'copied-message';
            document.body.appendChild(msg);
            requestAnimationFrame(() => msg.classList.add('visible'));
            setTimeout(() => {
                msg.classList.remove('visible');
                msg.addEventListener('transitionend', () => msg.remove(), { once: true });
            }, 2000);
        }
// });