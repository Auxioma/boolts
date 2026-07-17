    document.addEventListener('DOMContentLoaded', () => {
        const menuElement = document.querySelector('#mobile-menu');
        const menuButton = document.querySelector('#mobile-menu-button');

        if (!menuElement || !menuButton) {
            return;
        }

        if (typeof Mmenu === 'undefined') {
            console.error(
                'Mmenu est introuvable. Vérifie que les fichiers CSS et JavaScript sont chargés.'
            );

            return;
        }

        const menu = new Mmenu('#mobile-menu', {
            slidingSubmenus: false,

            offCanvas: {
                position: 'right-front'
            },

            theme: 'light'
        });

        const api = menu.API;

        menuButton.addEventListener('click', () => {
            api.open();
        });

        api.bind('open:after', () => {
            menuButton.setAttribute('aria-expanded', 'true');
            menuButton.setAttribute('aria-label', 'Fermer le menu');
        });

        api.bind('close:after', () => {
            menuButton.setAttribute('aria-expanded', 'false');
            menuButton.setAttribute('aria-label', 'Ouvrir le menu');
        });
    });