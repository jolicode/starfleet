window.addEventListener('load', function(e) {
    const url = new URL(window.location.href);
    const id = url.searchParams.get("highlight");

    if (!id) {
        return;
    }

    const targetElement = document.getElementById(id);
    targetElement.classList.add('highlight');

    targetElement.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'nearest',
    });
})
