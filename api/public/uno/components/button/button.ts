/**
 * Adds a11y attributes to a link which is styled as a button. Normally, links
 * are triggered with the [ENTER] key, while buttons as triggered with the
 * [SPACE] key. This scripts makes sure that links can also be triggered using
 * the [SPACE] key. Since there are no visual clues that the element is actually
 * an a element, rather than a button element, we have to mimic the behaviour of
 * the button element.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/ARIA_Techniques/Using_the_button_role
 */
import {Utils} from '../../core/utils';

export class Button {

    private keyDownListener: EventListenerObject;

    constructor(private element: HTMLElement) {
        this.keyDownListener = this.onKeyDown.bind(this);
        element.addEventListener('keydown', this.keyDownListener);
        element.setAttribute('role', 'button');

        if (!this.element.classList.contains('btn--disabled')) {
            element.setAttribute('tabindex', '0');
        }
    }

    /**
     * Removes all references to allow GC
     */
    destroy(): void {
        this.element.removeEventListener('keydown', this.keyDownListener);
    }

    private onKeyDown(event: KeyboardEvent): void {
        if (Utils.IsKeyPressed(event, ' ')) {
            event.preventDefault();

            if (this.element.classList.contains('btn--disabled')) {
                return;
            }

            if (typeof this.element.click === 'function') {
                this.element.click();
            } else {
                // Fallback for browsers which don't support click();
                let clickEvent: MouseEvent = new MouseEvent('click', {
                    bubbles: true,
                    cancelable: false,
                    view: window
                });

                this.element.dispatchEvent(clickEvent);

            }

        }
    }
}
