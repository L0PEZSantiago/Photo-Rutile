import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['overlay', 'toggle'];

    connect() {
        this._onKeydown = (e) => {
            if (e.key === 'Escape') this.close();
        };
        document.addEventListener('keydown', this._onKeydown);
    }

    disconnect() {
        document.removeEventListener('keydown', this._onKeydown);
    }

    toggle() {
        const isOpen = this.overlayTarget.classList.contains('is-open');
        isOpen ? this.close() : this.open();
    }

    open() {
        this.overlayTarget.classList.add('is-open');
        this.overlayTarget.setAttribute('aria-hidden', 'false');
        if (this.hasToggleTarget) {
            this.toggleTarget.setAttribute('aria-expanded', 'true');
        }
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.overlayTarget.classList.remove('is-open');
        this.overlayTarget.setAttribute('aria-hidden', 'true');
        if (this.hasToggleTarget) {
            this.toggleTarget.setAttribute('aria-expanded', 'false');
        }
        document.body.style.overflow = '';
    }
}
