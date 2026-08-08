const endpoint = document.querySelector('meta[name="frontend-error-endpoint"]')?.content;
const csrfToken = document.querySelector('meta[name="frontend-error-csrf-token"]')?.content;

function truncate(value, length) {
    return String(value ?? '').slice(0, length);
}

function normalizeUrl(value) {
    if (!value) {
        return null;
    }

    try {
        const url = new URL(value, window.location.origin);

        return url.origin === window.location.origin
            ? url.pathname
            : `${url.origin}${url.pathname}`;
    } catch {
        return truncate(value, 2_000);
    }
}

function report(payload) {
    if (!endpoint || !csrfToken) {
        return;
    }

    fetch(endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            ...payload,
            page: window.location.pathname,
        }),
    // Do not emit an unhandled rejection when the reporting endpoint is unavailable.
    }).catch(() => {});
}

window.addEventListener('error', (event) => {
    if (event instanceof ErrorEvent) {
        report({
            type: 'javascript',
            message: truncate(event.message, 2_000),
            source: normalizeUrl(event.filename),
            line: event.lineno,
            column: event.colno,
            stack: truncate(event.error?.stack, 8_000),
        });

        return;
    }

    const element = event.target;

    if (element instanceof HTMLLinkElement && element.rel === 'stylesheet') {
        report({
            type: 'css',
            message: 'Chargement de feuille de style échoué.',
            source: normalizeUrl(element.href),
        });
    } else if (element instanceof HTMLScriptElement) {
        report({
            type: 'javascript',
            message: 'Chargement de script échoué.',
            source: normalizeUrl(element.src),
        });
    } else if (element instanceof HTMLElement) {
        report({
            type: 'html',
            message: `Chargement de ressource HTML échoué (${element.tagName.toLowerCase()}).`,
            source: normalizeUrl(element.getAttribute('src') ?? element.getAttribute('href')),
        });
    }
}, true);

window.addEventListener('unhandledrejection', (event) => {
    const reason = event.reason;

    report({
        type: 'javascript',
        message: truncate(reason instanceof Error ? reason.message : reason, 2_000),
        stack: truncate(reason instanceof Error ? reason.stack : null, 8_000),
    });
});
