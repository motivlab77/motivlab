// No animations, only basic interactivity
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('portfolio-item')) {
        e.preventDefault();
        const lightbox = document.createElement('div');
        lightbox.className = 'fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50';
        const img = document.createElement('img');
        img.src = e.target.src;
        img.className = 'max-w-3xl max-h-3xl';
        const close = document.createElement('span');
        close.className = 'absolute top-4 right-4 text-white text-3xl cursor-pointer';
        close.innerHTML = '&times;';
        lightbox.appendChild(img);
        lightbox.appendChild(close);
        document.body.appendChild(lightbox);
        close.addEventListener('click', () => lightbox.remove());
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) lightbox.remove();
        });
    }
});
