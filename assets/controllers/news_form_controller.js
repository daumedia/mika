import { Controller } from '@hotwired/stimulus';

/*
 * News-Formular (Admin): Sprach-Tab-Umschaltung und Slug-Autogenerierung aus dem
 * englischen Titel. Ersetzt das frühere Inline-<script> (BF-07) — kein Inline-JS mehr,
 * damit eine strikte CSP (script-src 'self') greift.
 */
export default class extends Controller {
    static targets = ['tab', 'panel', 'title', 'slug'];

    connect() {
        // Bei einem bereits gefüllten Slug (Bearbeiten) nicht automatisch überschreiben.
        this.manual = this.hasSlugTarget && this.slugTarget.value.trim() !== '';
    }

    switchTab(event) {
        const lang = event.currentTarget.dataset.tab;

        this.tabTargets.forEach((tab) => {
            const active = tab.dataset.tab === lang;
            tab.classList.toggle('active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        this.panelTargets.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.tabPanel !== lang);
        });
    }

    markManual() {
        this.manual = true;
    }

    generateSlug() {
        if (this.manual || !this.hasSlugTarget || !this.hasTitleTarget) {
            return;
        }

        this.slugTarget.value = this.titleTarget.value
            .toLowerCase()
            .trim()
            .replace(/[àáâãäå]/g, 'a')
            .replace(/[èéêë]/g, 'e')
            .replace(/[ìíîï]/g, 'i')
            .replace(/[òóôõö]/g, 'o')
            .replace(/[ùúûü]/g, 'u')
            .replace(/[ñ]/g, 'n')
            .replace(/[ç]/g, 'c')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }
}
