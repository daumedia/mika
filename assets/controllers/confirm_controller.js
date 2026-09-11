import { Controller } from '@hotwired/stimulus';

/*
 * Bestätigung vor dem Absenden (z. B. Löschen). Ersetzt das frühere
 * onsubmit="return confirm(...)" (BF-07) — kein Inline-JS mehr.
 *
 * Nutzung: data-controller="confirm" data-action="submit->confirm#check"
 *          data-confirm-message-value="…?"
 */
export default class extends Controller {
    static values = { message: String };

    check(event) {
        if (!window.confirm(this.messageValue)) {
            event.preventDefault();
        }
    }
}
