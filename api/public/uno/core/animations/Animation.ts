import {Easings} from './Easings';

export abstract class Animation {

    private _ease: string = 'easeOutCubic';
    private _animationDuration: number = 30;

    private _step: number = 0;
    private _value: number;
    private _delta: number;
    private _initialValue: number;

    protected canAnimate: boolean;

    constructor(protected element: HTMLElement) {
        // Feature detection
        this.canAnimate = typeof window.requestAnimationFrame === 'function';
    }

    /**
     * The number of frames this animation runs
     * @default 30
     * @returns {number}
     */
    get animationDuration(): number {
        return this._animationDuration;
    }

    set animationDuration(value: number) {
        this._animationDuration = value;
    }

    /**
     * The easing equation to use
     * @return {string}
     */
    get ease(): string {
        return this._ease;
    }

    set ease(value: string) {
        if (Easings.hasOwnProperty(value)) {
            this._ease = value;
        } else {
            throw new Error(`Easing equation '${value}' does not exist`);
        }
    }

    /**
     * The current value / position of the animation
     * @return {number}
     */
    get value(): number {
        return this._value;
    }

    set value(value: number) {
        this._value = value;
    }

    /**
     * The change between the start & end position of the animation
     * @return {number}
     */
    get delta(): number {
        return this._delta;
    }

    set delta(value: number) {
        this._delta = value;
    }

    /**
     * The start value of the animation
     * @return {number}
     */
    get initialValue(): number {
        return this._initialValue;
    }

    set initialValue(value: number) {
        this._initialValue = value;
    }

    /**
     * Starts the animation, dispatches `uno-animation-start`
     */
    public start(): void {
        this.dispatch('uno-animation-start');
        if (this.canAnimate) {
            this._step = 0;
            this.element.classList.add('animating');
            requestAnimationFrame(this.progress.bind(this));
        } else {
            this.value = this.initialValue + this.delta;
            this.end();
        }
    }

    /**
     * @todo Implement
     */
    public cancel(): void {
        this.element.classList.remove('uno-animating');
        this.dispatch('uno-animation-cancel');
    }

    /**
     * Called on each iteration of the animation, dispatches `uno-animation-progress`
     */
    protected progress(): void {
        this.value = Easings[this.ease](
            Math.min(this._step++, this.animationDuration),
            this.initialValue,
            this.delta,
            this.animationDuration);

        this.dispatch('uno-animation-progress', this.value);
        if (this._step <= this.animationDuration) {
            requestAnimationFrame(this.progress.bind(this));
        } else {
            // Call end via an animationFrame to make sure it's executed
            // in the next 'tick'. Otherwise, it will be called before the
            // last progress event;
            requestAnimationFrame(this.end.bind(this));
        }
    }

    /**
     * Called when the animation is finished, dispatches `uno-animation-end`
     */
    protected end(): void {
        this.element.classList.remove('animating');
        this.dispatch('uno-animation-end');
    }

    /**
     * Dispatches an event on the host element
     * @param eventName The name of the event to dispatch
     * @param data Additional data to send with the event
     */
    private dispatch(eventName: String, data: any = null): void {
        let event: any = document.createEvent('CustomEvent');
        event.initCustomEvent(eventName, true, true, data);
        this.element.dispatchEvent(event);
    }

}
