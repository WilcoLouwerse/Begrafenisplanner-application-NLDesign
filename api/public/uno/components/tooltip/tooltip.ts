const ARROW_HEIGHT: number = 15;
const MOBILE_BREAKPOINT: number = 768;

// Template for our tooltip
const TOOLTIP: string = `
<div class="tooltip" hidden x-uno-tooltip-window>
    <div class="tooltip__backdrop"></div>
    <div class="tooltip__window">
        <div class="tooltip__body" role="status"></div>
        <div class="tooltip__footer" tabindex="0">
            <button class="tooltip__close-button"><span class="icon icon-cross" role="presentation"></span>sluit</button>
        </div>
    </div>
</div>`;

/**
 * Abstract class for tooltips.
 */
export class Tooltip {

    private openOnHover: boolean;
    private container: HTMLElement;
    private _content: string;
    private _tooltip: HTMLElement;
    private _parent: HTMLElement;
    private isHover: Boolean = false;
    private hostEvents: Array<any> = [];
    private stylesheet: CSSStyleSheet;

    public set content(value: string) {
        this._content = value ? value : '';
    }

    public get content(): string {
        return this._content;
    }

    constructor(private host:HTMLElement) {
        if (!host.hasAttribute('title')) {
            throw new Error(`Tooltip is missing a title attribute`);
        }

        this._content = host.getAttribute('title');
        host.removeAttribute('title');

        host.classList.add('tooltip-trigger');

        this.stylesheet = this.createStyleSheet().sheet as CSSStyleSheet;

        let wrapper: HTMLElement = document.createElement('div');
        wrapper.innerHTML = TOOLTIP;
        wrapper.setAttribute('aria-live', 'polite');
        this.container = wrapper.firstElementChild as HTMLElement;
        this._parent = wrapper;
        this._parent.classList.add('tooltip__parent');
        this._tooltip = this.container.querySelector('.tooltip__window') as HTMLElement;
        const closeButton: HTMLElement = this.container.querySelector('.tooltip__close-button') as HTMLElement;

        this.setHover();

        this.addListener(host, 'click', this.onHostClick.bind(this));
        this.addListener(host, 'mouseover', this.onHostMouseOver.bind(this));
        this.addListener(host, 'mouseout', this.onHostMouseOut.bind(this));
        this.addListener(host, 'focus', this.onFocus.bind(this));
        this.addListener(host, 'blur', this.onBlur.bind(this));
        this.addListener(closeButton, 'click', this.onClick.bind(this));

        host.parentNode.insertBefore(this._parent, host.nextSibling);
    }

    private setContent(): void {

        const tooltipBody: HTMLElement = this.container.querySelector('.tooltip__body') as HTMLElement;

        if (tooltipBody) {
            tooltipBody.innerHTML = this._content;
        }
    }

    private setHover(): void {
        if (window.matchMedia('(min-width: 768px)').matches) {
            this.openOnHover = true;
        } else {
            this.openOnHover = false;
        }
    }

    private onClick(evt: Event): void {
        this.hide();
    }

    /**
     * Called when the user clicks the host. Set hover to false to prevent mouse
     * Mouse move events
     * @param evt
     */
    private onHostClick(evt: Event): void {
        if (!this.openOnHover) {
            this.isHover = false;
            this.show();
        }
    }

    private onFocus(evt: KeyboardEvent): void {
        this.show();
    }

    private onBlur(evt: KeyboardEvent): void {
        this.hide();
    }

    /**
     * Called when the user hovers over the host component, cancelled when a
     * touch event precedes this event.
     * @param evt
     */
    private onHostMouseOver(evt: Event): void {
        if (!this.openOnHover) {
            evt.preventDefault();
        } else {
            this.isHover = true;
            this.show();
        }
    }

    /**
     * Called when the mouse leaves the component, cancelled when a touch event
     * precedes this event
     * @param evt
     */
    private onHostMouseOut(evt: Event): void {
        if (!this.openOnHover) {
            evt.preventDefault();
        } else if (this.isHover) {
            this.hide();
        }
    }

    public destroy(): void {
        // Stop listening for events
        this.hide();
        this.hostEvents.forEach((ref: any) => {
            ref.element.removeEventListener(ref.event, ref.listener);
        });

        this.hostEvents = [];

    }

    /**
     * Hides the tooltip and restores the focus
     */
    private hide(): void {
        this.container.classList.remove('tooltip--open');
        this.container.setAttribute('hidden', '');
        // op kleine schermen waar een backdrop getoond wordt, scroll onderdrukken
        if (!(window.matchMedia('(min-width: 768px)').matches)) {
            document.querySelector('body').classList.remove('no-scroll');
        }

        this.isHover = false;
    }

    private show(): void {
        let openTooltips:NodeList = document.querySelectorAll('.tooltip--open');
        for (let i:number = 0; i < openTooltips.length; i ++){
            let e:HTMLElement = openTooltips[i] as HTMLElement;
            while (e.classList.contains('tooltip--open')) {
                e.classList.remove('tooltip--open');
            }
        }

        this.container.classList.add('tooltip--open');
        this.container.removeAttribute('hidden');

        this.setContent();

        let tooltipPosition: string = this.positionElement(this.host, this._tooltip);
        this._tooltip.classList.remove(`tooltip__window--top`);
        this._tooltip.classList.remove(`tooltip__window--bottom`);
        this._tooltip.classList.add(`tooltip__window--${tooltipPosition}`);

        // Trigger reflow
        /* tslint:disable */
        void this.host.offsetWidth;
        /* tslint:enable */

        // Now animate
        this._tooltip.classList.add('tooltip__window--animate');
        this._tooltip.classList.add('tooltip__window--show');

        if (!this.openOnHover) {
            (this._tooltip.querySelector('.tooltip__close-button') as HTMLElement).focus();
        }
        // op kleine schermen waar een backdrop getoond wordt, scroll onderdrukken
        if (!(window.matchMedia('(min-width: 768px)').matches)) {
            document.querySelector('body').classList.add('no-scroll');
        }
    }

    /**
     * Creates a stylesheet in the head section of the page. Only 1 stylesheet
     * per page will be created
     * @return {HTMLStyleElement}
     */
    private createStyleSheet(): HTMLStyleElement {
        let sheet: HTMLStyleElement = document.querySelector('style[x-uno-tooltip-stylesheet]') as HTMLStyleElement;
        if (!sheet) {
            sheet = document.createElement('style');
            sheet.setAttribute('x-uno-tooltip-stylesheet', '');
            document.head.appendChild(sheet);
        }
        return sheet;
    }

    /**
     * Calculates the position of the tooltip
     */
    private positionElement(host: HTMLElement, target: HTMLElement): string {
        if (window.innerWidth > MOBILE_BREAKPOINT) {
            // pre existing rule might no longer be valid, so always remove
            // if necessary it will be added later
            if (this.stylesheet.rules && this.stylesheet.rules.length > 0) {
                this.stylesheet.removeRule(0);
            }

            let tooltipHeight: number = parseInt(getComputedStyle(target).height);
            let tooltipWidth: number = parseInt(getComputedStyle(target).width);

            let bodyRect: ClientRect = document.body.getBoundingClientRect();
            let hostRect: ClientRect = host.getBoundingClientRect();

            let tooltipPosition: string = 'top';

            // Calculate desired tooltip position. Default is above the host
            let x: number = -hostRect.width / 2 - tooltipWidth / 2;
            let y: number = -tooltipHeight - ARROW_HEIGHT - 20;

            let dx: number; // variable used if position is too much to the left or right

            if (hostRect.top - tooltipHeight < 10) {
                // Not enough space at the top, position below the host
                y = hostRect.height;
                tooltipPosition = 'bottom';
            }

            if (hostRect.right + x < 0) {
                // Element positioned too far to the left (offscreen)
                // Position the tooltip 20px from the left of the screen
                dx = -hostRect.right - x;
                x = 20 + x + dx; // 20 - hostRect.width;
                // reposition the arrow as well, default left is 135 px
                let arrowOffset: number = 135 - dx - 20;
                this.stylesheet.insertRule(`.tooltip__window::before { left: ${arrowOffset}px }`, 0);

            } else if (hostRect.right + x + tooltipWidth > bodyRect.width) {
                // Element positioned too far to the right
                // Position the tooltip 10px from the right of the screen
                dx = bodyRect.width - hostRect.right - tooltipWidth - x;
                x = x + dx - 10;
                // reposition the arrow as well, default left is 135 px
                let arrowOffset: number = 135 - dx + 10;
                this.stylesheet.insertRule(`.tooltip__window::before { left: ${arrowOffset}px }`, 0);
            }

            target.style.left = `${Math.round(x)}px`;
            target.style.top = `${Math.round(y)}px`;
            return tooltipPosition;
        }
    }

    /**
     * Adds an eventlistener to the element, while storing a reference to the
     * listener.
     * @param element
     * @param event
     * @param listener
     */
    private addListener(element: Node, event: string, listener: EventListenerObject): void {
        this.hostEvents.push({
            element: element,
            event: event,
            listener: listener
        });

        element.addEventListener(event, listener);
    }
}
