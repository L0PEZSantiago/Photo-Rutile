import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this._onKeydown = (e) => { if (e.key === 'Escape') this.close(); };
    }

    open(event) {
        const img = event.currentTarget;
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.innerHTML = `
            <img src="${img.dataset.imgNativeUrl}" alt="${img.alt}" class="image-modal-img">
            <button class="image-modal-close" aria-label="Fermer">&times;</button>
        `;
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.classList.contains('image-modal-close')) {
                this.close();
            }
        });
        document.body.appendChild(modal);
        document.addEventListener('keydown', this._onKeydown);
        modal.offsetHeight; // force reflow
        modal.classList.add('image-modal--visible');
        this._modal = modal;
    }

    close() {
        if (!this._modal) return;
        this._modal.remove();
        this._modal = null;
        document.removeEventListener('keydown', this._onKeydown);
    }
}
