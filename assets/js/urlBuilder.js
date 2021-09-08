const urls = document.getElementsByClassName('js-build-url');

if (urls.length) {
    const currentUrl = new URL(window.location.href);

    [...urls].forEach(form => {
        const targetUrl = form.dataset.action;
        const newUrl = new URL(targetUrl);

        newUrl.searchParams.append('previousUrl', currentUrl);
        form.action = newUrl;
    });
}
