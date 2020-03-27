import {Animation} from './Animation';

/**
 * Animation class which adds a slide-toggle effect to the supplied element
 * Example:
 * <code>
 *     var animation = new SlideToggle(document.getElementById('target'));
 *     animation.toggle();
 * </code>
 */
export class SlideToggle extends Animation {

    private elementHeight: number;

    private _toggleClass: string = 'animate--open';

    constructor(protected element: HTMLElement) {
        super(element);
    }

    /**
     * Class which gets applied when the element is 'toggled'
     * @default 'animate--open'
     * @returns {string}
     */
    get toggleClass(): string {
        return this._toggleClass;
    }

    set toggleClass(value: string) {
        this._toggleClass = value;
    }

    /**
     * Toggles the element, animates between height = 0 and the calculated
     * height of the element. When the previous animation hasn't finished yet,
     * it will animate from the point where the previous animation stopped.
     *
     * @return boolean The state to transform to (toggled or not)
     */
    public toggle(force: boolean = undefined): boolean {
        this.setMaxHeight();
        this.initialValue = parseInt(this.element.style.maxHeight);
        this.initialValue = isNaN(this.initialValue) ? 0 : this.initialValue;

        let toggled: boolean = this.element.classList.toggle(this.toggleClass);

        // No IE support for 2nd param for toggle :(
        if (force === true) {
            this.element.classList.add(this.toggleClass);
            toggled = force;
        } else if (force === false) {
            this.element.classList.remove(this.toggleClass);
            toggled = force;
        }

        // Remove the maxHeightProperty to correctly calculate the height
        // Round up a float to prevent missing that one pixel at the bottom
        this.element.style.maxHeight = '';
        this.elementHeight = toggled ? Math.ceil(parseFloat(getComputedStyle(this.element).height)) : 0;

        this.start();

        return toggled;
    }

    start(): void {
        this.element.style.maxHeight = this.initialValue + 'px';
        this.element.classList.add(this.toggleClass);
        this.delta = this.elementHeight - this.initialValue;
        super.start();
    }

    progress(): void {
        super.progress();
        this.element.style.maxHeight = Math.floor(this.value) + 'px';
    }

    end(): void {
        if (Math.floor(this.value) === 0) {
            this.element.classList.remove(this.toggleClass);
        }

        this.element.style.maxHeight = '';
        super.end();
    }

    /**
     * @private
     * Sets the maxHeight property to the initial height.
     */
    private setMaxHeight(): void {
        this.element.style.maxHeight = '';
        let toggled: boolean = this.element.classList.contains(this._toggleClass),
            height: number = toggled ? parseInt(getComputedStyle(this.element).height) : 0;

        this.element.style.maxHeight = height + 'px';
    }
}
