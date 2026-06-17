import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.searchTimeout = null;
        this.activeTheme = 'all';
        this._onFilterClick = this.handleFilterClick.bind(this);
        const filters = this.element.querySelector('.filters');
        if (filters) {
            filters.addEventListener('click', this._onFilterClick);
        }
    }

    disconnect() {
        clearTimeout(this.searchTimeout);
        const filters = this.element.querySelector('.filters');
        if (filters && this._onFilterClick) {
            filters.removeEventListener('click', this._onFilterClick);
        }
    }

    handleFilterClick(event) {
        const button = event.target.closest('.filter-button');
        if (!button) {
            return;
        }
        const theme = button.dataset.theme;
        if (theme === undefined) {
            return;
        }

        this.activeTheme = theme === 'all' ? 'all' : theme;

        this.element.querySelectorAll('.filter-button').forEach((btn) => {
            btn.classList.remove('active');
        });
        button.classList.add('active');

        this.refreshList();
    }

    async refreshList() {
        const creationsContainer = this.element.querySelector('.all-creations');
        if (!creationsContainer) {
            return;
        }

        const input = this.element.querySelector('.search-container input[type="text"]');
        const q = input ? input.value.trim() : '';

        const params = new URLSearchParams();
        if (q) {
            params.set('q', q);
        }
        if (this.activeTheme && this.activeTheme !== 'all') {
            params.set('theme', this.activeTheme);
        }

        const queryString = params.toString();
        const url = queryString ? `/creations/search?${queryString}` : '/creations/search';

        try {
            const response = await fetch(url);
            const html = await response.text();
            creationsContainer.innerHTML = html;
        } catch (error) {
            console.error('Erreur lors du chargement:', error);
        }
    }

    async searchByName() {
        await this.refreshList();
    }

    searchByNameWithDebounce() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.searchByName();
        }, 300);
    }
}
