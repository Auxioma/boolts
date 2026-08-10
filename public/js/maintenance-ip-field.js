document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-current-ip]').forEach((ipField) => {
        const currentIp = ipField.dataset.currentIp;

        if (!currentIp || ipField.dataset.currentIpButtonInitialized) {
            return;
        }

        const inputGroup = document.createElement('div');
        inputGroup.classList.add('input-group');

        const button = document.createElement('button');
        button.type = 'button';
        button.classList.add('btn', 'btn-outline-secondary');
        button.textContent = 'Utiliser mon IP';
        button.addEventListener('click', () => {
            ipField.value = currentIp;
            ipField.dispatchEvent(new Event('input', { bubbles: true }));
            ipField.dispatchEvent(new Event('change', { bubbles: true }));
            ipField.focus();
        });

        ipField.dataset.currentIpButtonInitialized = 'true';
        ipField.parentNode.insertBefore(inputGroup, ipField);
        inputGroup.append(ipField, button);
    });
});
