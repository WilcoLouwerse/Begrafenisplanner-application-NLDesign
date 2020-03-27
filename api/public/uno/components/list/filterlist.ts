const FILTER_BUTTON_TEMPLATE: string = `<button class="list--filter-button">
    <i class="icon icon-cross"></i>
</button>`;

/**
 * @class FilterList
 */
export class FilterList {

    constructor(private host: Element, private elements: Array<any>) {
        if (host  && host.nodeName === 'UL') {
            this.createElements();
        } else {
            throw new Error('There is no correct host element specified');
        }
    }

    /**
     * Scaffolding
     */
    private createElements(): void {
        for (let i: number = 0, j: number = this.elements.length; i < j; i += 1) {
            const value: string = this.elements[i].value;
            const id: string = this.elements[i].id;
            const element: HTMLElement = this.buildElement(value, id);

            this.host.appendChild(element);
        }
    }

    /**
     * Build the filter element
     * @param value the value of the filter element
     * @param id the id of the filter element
     */
    private buildElement(value: string, id: string): HTMLElement {
        let el: HTMLElement = document.createElement('li');
        el.innerHTML = value + FILTER_BUTTON_TEMPLATE;
        el.id = id;
        el.tabIndex = 0;
        el = this.addClickListener(el);

        return el;
    }

    /**
     * Add a listener to the delete button of the filter element
     * @param el the filter element which button needs a delete listener
     */
    private addClickListener(el: HTMLElement): HTMLElement {
        const button: HTMLElement = el.querySelector('.list--filter-button') as HTMLElement;
        button.addEventListener('click', this);

        return el;
    }

    /**
     * Delete a single filter element
     * @param el the element that needs deleting.
     * @fires 'filterlist-deleted-item'
     */
    delete(child: HTMLElement): void {
        this.host.removeChild(child);
        const event: any = this.createEvent('filterlist-deleted-item');
        event.data = {id: child.id};
        this.host.dispatchEvent(event);
    }

    /**
     * Delete all filter elements
     * @fires 'filterlist-deleted'
     */
    deleteAll(): void {
        const node: Element = this.host;

        while (node.firstChild) {
            node.removeChild(node.firstChild);
        }

        if (!node.firstChild) {
            const event: any = this.createEvent('filterlist-deleted');
            this.host.dispatchEvent(event);
        }
    }

    /**
     * Creates an event
     * @param type the type of the event
     */
    private createEvent(type: string, bubble: boolean = true, cancelable: boolean = true): Event {
        const event: any = document.createEvent('CustomEvent');
        event.initEvent(type, bubble, cancelable);

        return event;
    }

    /**
     * Handle events for event listeners
     * @param event the event
     */
    handleEvent(event: Event): void {
        const target: HTMLElement = event.currentTarget as HTMLElement;

        if (target.classList.contains('list--filter-button')) {
            this.delete(target.parentElement);
        }
    }

    /**
     * Remove listeners and filter elements
     */
    destroy(): void {
        this.deleteAll();
    }
}
