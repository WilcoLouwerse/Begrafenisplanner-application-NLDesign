import { SlideToggle } from '../../core/animations/SlideToggle';
import { Utils } from './../../core/utils';

const TOGGLE_CLASS: string = 'collapse__details--open';
const TOGGLE_TITLE_CLASS: string = 'collapse__title--open';

/**
 * @class Collapse
 * Creates a summary / details like element, which opens the details on click
 * of the title.
 * Example:
 * <code>
 *     <div class="collapse">
 *          <div x-uno-collapse>
 *              <i class="collapse__indicator"></i>
 *              <span class="collapse__title">Titel van collapse</span>
 *          </div>
 *          <div class="collapse__details">
 *              Humani generiss sunt dominas de nobilis compater.
 *              Cliniass cadunt in audax cirpi! Est placidus urbs, cesaris.
 *          </div>
 *      </div>
 *      <script>
 *          // Load collapse component
 *          System.import('/uno/components/collapse/collapse.js').then(function (module) {
 *             // Select all <details> elements on the page
 *              var collapses = document.querySelectorAll('[x-uno-collapse]');
 *              // Initialize all collapses
 *              for (var i = 0; i < collapses.length; i++) {
 *                 new module.Collapse(collapses.item(i));
 *              }
 *          });
 *      </script>
 * </code>
 */
export class Collapse {

    private animate: SlideToggle;

    private details: HTMLElement;
    private detailsSibling: HTMLElement;

    private groupedCollapses: Array<Node>;

    /**
     * @constructor
     * @param host Element The element which contains a x-uno-collapse attribute
     */
    constructor(private host: Element) {

        if (!host) {
            return;
        }

        if ((this.host as any).hasOwnProperty('unoCollapse')) {
            // This element is already initialized
            return;
        }
        (this.host as any).unoCollapse = true;

        if (!host.hasAttribute('x-uno-collapse')) {
            // The attribute is mandatory
            host.setAttribute('x-uno-collapse', '');
        }

        if (host.hasAttribute('data-collapse-target')) {
            console.warn('[data-collapse-target] is deprecated and will be removed in version 4 in favor of [x-uno-collapse-target]');
        }

        if (host.hasAttribute('data-collapse-group')) {
            console.warn('[data-collapse-group] is deprecated and will be removed in version 4 in favor of [x-uno-collapse-group]');
        }

        this.details = this.findTarget(host);

        this.host.addEventListener('click', this);
        this.host.addEventListener('keydown', this);
        this.host.addEventListener('collapse-open', this);
        this.host.addEventListener('collapse-close', this);

        this.details.addEventListener('keydown', (event: KeyboardEvent) => {
            if (Utils.IsKeyPressed(event, 'Escape') || Utils.IsKeyPressed(event, '')) {
                this.close();
            }
            if (Utils.IsKeyPressed(event, 'Tab')) {
                Utils.FocusChild(event, this.details.querySelectorAll('li a'));
            }
        });

        this.setupAnimation();
        this.groupCollapses(host);

    }

    /**
     * Closes the collapse
     */
    close(): void {
        if (this.details.classList.contains(TOGGLE_CLASS)) {
            let event: any = document.createEvent('CustomEvent');
            event.initCustomEvent('collapse-close', true, true, this.details);
            this.host.dispatchEvent(event);
            let hostElement: HTMLElement = this.host as HTMLElement;
            hostElement.focus();
        }
    }

    /**
     * Opens the collapse
     */
    open(): void {
        if (!this.details.classList.contains(TOGGLE_CLASS)) {
            let event: any = document.createEvent('CustomEvent');
            event.initCustomEvent('collapse-open', true, true, this.details);
            this.host.dispatchEvent(event);
            this.focusFirstChild();
        }
    }

    /**
     * Focuses the first child
     */
    focusFirstChild(): void {
        let firstChild: HTMLElement = this.details.querySelector('li a') as HTMLElement;
        if (firstChild) {
            firstChild.focus();
        }
    }

    /**
     * Removes all references to allow GC
     */
    destroy(): void {
        this.host.removeEventListener('collapse-open', this);
        this.host.removeEventListener('collapse-close', this);
        this.host.removeEventListener('click', this);
        this.host.removeEventListener('keydown', this);

        delete (this.host as any).unoCollapse;
        this.details.removeEventListener('keydown', this);
        this.details.removeEventListener('uno-animation-end', this);
        this.details.removeEventListener('uno-animation-progress', this);

        if (this.groupedCollapses) {
            this.groupedCollapses.forEach((collapse: Element) => {
                collapse.removeEventListener('collapse-open', this);
            });

            this.groupedCollapses = null;
        }
    }

    /**
     * Handles all events for this component
     * @private
     * @param {Event} event
     */
    handleEvent(event: Event): void {

        switch (event.currentTarget) {
            case this.host:
                switch (event.type) {
                    case 'click':
                        this.onTitleClick();
                        break;
                    case 'keydown':
                        this.onTitleKeyDown(event as KeyboardEvent);
                        break;
                    case 'collapse-open':
                        this.onCollapseOpen(event);
                        break;
                    case 'collapse-close':
                        this.onCollapseClose(event);
                        break;
                    default:
                    // nothing
                }
                break;
            case this.details:
                let eventType: string = Utils.CamelCase(event.type);
                this[`onDetails${eventType}`](event);

                break;
            default:
                if (event.type === 'collapse-open') {
                    this.onCollapseGroupOpen(event);
                }
        }
    }

    /**
     * Opens / closes the details. The open / close is done through the
     * animation class.
     */
    toggle(): void {
        this.details = this.findTarget(this.host);

        if (this.details.classList.contains(TOGGLE_CLASS)) {
            this.close();
        } else {
            this.open();
        }
    }

    private onCollapseOpen(event: Event): void {
        if (!event.defaultPrevented) {
            this.animate.toggle(true);
        }
    }

    private onCollapseClose(event: Event): void {
        if (!event.defaultPrevented) {
            this.animate.toggle(false);
        }
    }

    /**
     * Called when a collapse in the same group is opened. Close other collapses
     * @param {CustomEvent} evt
     */
    private onCollapseGroupOpen(evt: Event): void {
        if (evt.currentTarget !== this.host && (evt as CustomEvent).detail !== this.details) {
            this.close();
        }
    }

    /**
     * Called when the user clicks the .collapse__title
     */
    private onTitleClick(): void {
        this.toggle();
    }

    /**
     * Add keyboard support, toggle details on ENTER or SPACE key
     * @param event
     */
    private onTitleKeyDown(event: KeyboardEvent): void {
        if (Utils.IsKeyPressed(event, 'Enter') || Utils.IsKeyPressed(event, ' ')) {
            event.preventDefault();
            this.toggle();
        }
    }

    /**
     * Callback for when the animation has finished
     */
    private onDetailsUnoAnimationEnd(): void {
        // Update aria attributes
        let isVisible: Boolean = this.details.classList.contains(TOGGLE_CLASS);

        this.host.classList.remove(TOGGLE_TITLE_CLASS);
        if (isVisible) {
            this.host.classList.add(TOGGLE_TITLE_CLASS);
        }
        this.host.setAttribute('aria-expanded', isVisible.toString());
        this.details.setAttribute('aria-expanded', isVisible.toString());
    }

    private onDetailsUnoAnimationProgress(event: CustomEvent): void {
        this.detailsSibling.style.paddingTop = Math.round(event.detail) + 'px';
    }

    /**
     * Finds the target for the collapse. The collapse is either found by
     * - id, specified in x-uno-collapse-target
     * - id, specified in data-collapse-target
     * - location in dom, next to the host.
     * @param host
     * @return {HTMLElement}
     */
    private findTarget(host: Element): HTMLElement {
        let target: Element;

        if (host.hasAttribute('x-uno-collapse-target')) {
            target = document.getElementById(host.getAttribute('x-uno-collapse-target'));
        } else if (host.hasAttribute('data-collapse-target')) {
            // For backwards compatibility
            target = document.getElementById(host.getAttribute('data-collapse-target'));
        } else if (host.nextElementSibling && host.nextElementSibling.classList.contains('collapse__details')) {
            target = host.nextElementSibling;
        }

        if (!target) {
            throw new Error(`Collapse cannot find collapse target`);
        }

        // Set an id if not present. This is needed for ARIA attributes
        if (!target.hasAttribute('id')) {
            target.setAttribute('id', Utils.GenerateUID());
        }

        if (target.classList.contains(TOGGLE_CLASS)) {
            this.host.classList.add(TOGGLE_TITLE_CLASS);
        }

        this.setAria(target);

        return target as HTMLElement;
    }

    /**
     * Groups all collapses bases on either
     * - An attribute on each collapse, containing a group identifier (x-uno-collapse-group="{GROUPNAME}")
     * - An attribute on a wrapping element, containing the attribute x-uno-collapse
     * @param host
     */
    private groupCollapses(host: Element): void {
        let collapses: NodeList,
            groupAttributes: Array<string> = ['x-uno-collapse-group', 'data-collapse-group'],
            hostHasGroupAttribute: boolean = false;

        // Check both flavors for backwards compatibility
        groupAttributes.forEach((attribute: string) => {
            if (host.hasAttribute(attribute)) {
                let groupId: string = host.getAttribute(attribute);
                collapses = document.querySelectorAll(`[${attribute}="${groupId}"]`);
                hostHasGroupAttribute = true;
            }
        });

        if (!hostHasGroupAttribute) {
            // Group attribute not found on host, traverse the dom up to the
            // <body> tag, checking if any parent contains the group attribute.
            while (host.parentElement && !collapses) {
                host = host.parentElement;

                groupAttributes.forEach((attribute: string) => {
                    if (host.hasAttribute(attribute)) {
                        // Group found, select all collapses inside group element
                        collapses = host.querySelectorAll(`[x-uno-collapse]`);
                    }
                });

            }
        }

        if (collapses) {
            this.groupedCollapses = [];

            for (let i: number = 0; i < collapses.length; i++) {
                this.groupedCollapses.push(collapses.item(i));
                collapses.item(i).addEventListener('collapse-open', this);
            }
        }
    }

    private setupAnimation(): void {

        this.animate = new SlideToggle(this.details as HTMLElement);
        this.animate.toggleClass = TOGGLE_CLASS;
        this.animate.animationDuration = 1;

        this.details.addEventListener('uno-animation-end', this);

        if (this.details.nodeName.toLowerCase() === 'tr') {
            // We're animating a table, some extra work required here
            this.details.addEventListener('uno-animation-progress', this);
            this.detailsSibling = document.createElement('tr');
            this.detailsSibling.classList.add('collapse__table-divider');
            this.detailsSibling.innerHTML = '<td></td>';
            if (this.details.nextElementSibling) {
                this.details.parentNode.insertBefore(this.detailsSibling, this.details.nextElementSibling);
            } else {
                this.details.parentNode.appendChild(this.detailsSibling);
            }
            this.detailsSibling = this.detailsSibling.firstElementChild as HTMLElement;
        }
    }

    /**
     * Adds aria attributes to host & target element
     */
    private setAria(target: Element): void {
        let open: boolean = target.classList.contains(TOGGLE_CLASS);
        target.setAttribute('aria-expanded', open.toString());

        // Do not override default region
        if (!target.hasAttribute('role')) {
            target.setAttribute('role', 'region');
        }

        this.host.setAttribute('role', 'button');
        this.host.setAttribute('tabindex', '0');
        this.host.setAttribute('aria-expanded', open.toString());
        this.host.setAttribute('aria-controls', target.getAttribute('id'));
    }
}
