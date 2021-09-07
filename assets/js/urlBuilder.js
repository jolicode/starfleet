const allUrlToBuild = document.getElementsByClassName('js-build-url');

if (allUrlToBuild.length) {
    const currentUrl = new URL(window.location.href);

    [...allUrlToBuild].forEach(form => {
        const targetUrl = form.dataset.action;
        const newUrl = new URL(targetUrl);

        newUrl.searchParams.append('previousUrl', currentUrl);
        form.action = newUrl;
    });
}
