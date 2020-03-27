import { Utils } from '../../core/utils';

const ITEM_TEMPLATE: string = `<li role="button" tabindex="-1" class="combobox__item">
    <span class="combobox__link">%s</span>
 </li>`;

const NO_RESULT_TEMPLATE: string = `<li tabindex="-1" class="combobox__item combobox__item--no-results" hidden>
 <span class="combobox__link">Geen resultaten gevonden.</span>
</li>`;

const ITEM_COMBOBOX_TEMPLATE: string = `<li class="input__group input__group--compact input__group--checkbox" role="group">
<input role=checkbox type="checkbox" id="chkbx-compact-$id" class="input__control input__control--checkbox">
<label class="input__label input__label--checkbox combobox__link" for="chkbx-compact-$id">$label</label>
</li>`;

const DATAFIELD: string = 'comboboxItem';

export const MODE_FILTER: number = 1;
export const MODE_AUTOCOMPLETE: number = 2;

/**
 * Combobox / autocomplete component
 * @version 1.0.0
 *
 * <div class="input combobox">
 *  <label class="input__label">Opleiding</label>
 *  <div class="input__hint">Voer een opleidingsnaam of crebonummer in</div>
 *  <input type="text" class="combobox__input input__control input__control--text input__control--xl input__control--select">
 * </div>
 */
export class Combobox implements EventListenerObject {

    private _allowUnknown: Boolean = true;
    private _data: Array<any>;
    private _filterFunction: any = this.defaultFilter;
    private _icon: HTMLElement;
    private _initTimeoutId: number;
    private _initTimeout: number = 2;
    private _inputTimeout: number = 100;
    private _inputTimeoutId: number;
    private _input: HTMLInputElement;
    private _list: HTMLElement;
    private _labelField: string;
    private _labelFunction: any = this.defaultLabel;
    private _loading: boolean = false;
    private _mode: number = MODE_FILTER;
    private _prevQuery: string;
    private _toggle: Element;
    private _validationError: string = 'Ongeldige invoer';
    private _value: any = null;
    private _filterContainer: HTMLElement = null;
    /**
     * Highlight matched text? Only on default filter
     * @type {boolean}
     * @private
     */
    private _highlight: boolean = true;

    constructor(private host: Element, private isCheckboxFilter: Boolean = false) {

        if (!host) {
            throw new Error('No host element specified');
        }

        this.setup();
        this.setupListeners();
    }

    /**
     * Indicates whether or not an entered value must be present in the
     * data list.
     * @default true
     * @return {Boolean}
     */
    get allowUnknown(): Boolean {
        return this._allowUnknown;
    }

    set allowUnknown(value: Boolean) {
        this._allowUnknown = value;
    }

    get data(): Array<any> {
        return this._data;
    }

    /**
     * Sets the values of the combobox, can be an array of strings or
     * an array objects. When using objects, be sure to set the `labelField`
     * property
     * @param {Array<any>} value
     */
    set data(value: Array<any>) {
        this._data = value;
        this.initialize();
    }

    /**
     * A custom filter function for the data array.
     * Signature: function(element: any, index: number, array: any[]): boolean
     * @see https://developer.mozilla.org/nl/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
     * @return {Function}
     */
    get filterFunction(): any {
        return this._filterFunction;
    }

    set filterFunction(value: any) {
        this._highlight = false;
        this._filterFunction = value;
    }

    /**
     * The number of milliseconds to wait before applying the filtering after the user
     * has changed the input
     * @return {string}
     */
    get inputTimeout(): number {
        return this._inputTimeout;
    }

    set inputTimeout(value: number) {
        this._inputTimeout = value;
    }
    /**
     * Sets which property of the objects to use to display in the list
     * @return {string}
     */
    get labelField(): string {
        return this._labelField;
    }

    set labelField(value: string) {
        this._labelField = value;
        this.initialize();
    }

    /**
     * Sets which property of the objects to use to display in the list. May contain HTML
     * Signature: function(value: any): string
     * @return {function}
     */
    get labelFunction(): any {
        return this._labelFunction;
    }

    set labelFunction(value: any) {
        this._labelFunction = value;
        this.initialize();
    }

    /**
     * Indicates the combobox is loading data
     * @return {boolean}
     */
    get loading(): boolean {
        return this._loading;
    }

    set loading(isLoading: boolean) {
        this._loading = isLoading;
        if (this._icon) {
            let action: string = isLoading ? 'add' : 'remove';
            this._icon.classList[action]('combobox__icon--loading');
        }
    }

    /**
     * Set the mode of the combobox
     * MODE_AUTOCOMPLETE: Always show the full list
     * MODE_FILTER: Filter the list based on the input value
     * @return {number}
     */
    get mode(): number {
        return this._mode;
    }

    set mode(value: number) {
        this._mode = value;
    }

    /**
     * Set the error which is shown when the form is validated and the value
     * of the combobox is invalid
     * @return {string}
     */
    get validationError(): string {
        return this._validationError;
    }

    set validationError(value: string) {
        this._validationError = value;
    }

    get value(): any {
        return this._value;
    }

    /**
     * Sets the current selected value of the combobox
     * @param value
     */
    set value(value: any) {
        if (!this.allowUnknown && this.data.indexOf(value) === -1) {
            throw new Error(`Unknown item '${value.toString()}'`);
        }
        this._value = value;
        const el: HTMLElement = document.createElement('div');
        el.innerHTML = this.labelFunction(value);
        this._input.value = el.textContent;
    }

    /**
     * Indicates if the list of the combobox is visible to the user
     * @readonly
     * @return {boolean}
     */
    get isOpen(): boolean {
        return this.host.classList.contains('combobox--autocomplete-open');
    }

    /**
     * Returns the current value of the input
     * @return {string}
     */
    get query(): string {
        return this._input ? this._input.value : undefined;
    }

    /**
     * @private
     * @param {Event} event
     */
    handleEvent(event: Event): void {

        switch (event.currentTarget) {
            case this._toggle:
                this[`onToggle${event.type}`](event);
                break;
            case this._list:
                if (!this.isCheckboxFilter) {
                    this[`onList${event.type}`](event);
                }
                break;
            case this._input:
                this[`onInput${event.type}`](event);
                break;
            case document.body:
                this[`onBody${event.type}`](event);
                break;

            default:
            /* nothing */

        }
    }

    /**
     * Opens the dropdown
     * @fires 'combobox-open'
     */
    open(): void {
        if (!this.data) {
            return;
        }

        const event: any = document.createEvent('CustomEvent');
        event.initEvent('combobox-open', true, true);

        if (this.host.dispatchEvent(event)) {
            if (!this.isOpen) {
                // (Re-)opening the combobox. Reset the query to force filtering
                this._prevQuery = null;
            }
            this.host.classList.add('combobox--autocomplete-open');
            this._list.hidden = false;
            this.filterInput();
        }
    }

    /**
     * Closes the dropdown
     * @fires 'combobox-close'
     */
    close(): void {

        if (!this.isOpen) {
            return;
        }
        const event: any = document.createEvent('CustomEvent');
        event.initEvent('combobox-close', true, true);

        if (this.host.dispatchEvent(event)) {
            this.host.classList.remove('combobox--autocomplete-open');
            this._list.hidden = true;
        }
    }

    /**
     * Call when the combobox is removed from the page
     * Clean up listeners
     */
    destroy(): void {
        this._toggle.removeEventListener('click', this);

        this._input.removeEventListener('keyup', this);
        this._input.removeEventListener('keydown', this);
        this._input.removeEventListener('input', this);

        this._list.removeEventListener('click', this);
        this._list.removeEventListener('keyup', this);
        this._list.removeEventListener('keydown', this);
        this._list.remove();

        document.body.removeEventListener('click', this);
    }

    /**
     * Create the listitems based on the data array
     */
    private createListItems(): void {
        if (this.isCheckboxFilter) {

            this.data.forEach((item: any, index: number) => {
                let el: Element = Utils.CreateNode(ITEM_COMBOBOX_TEMPLATE
                    .replace('$label', item.name)
                    .replace(/\$id/g, index.toString()));
                el[DATAFIELD] = item;

                el.querySelector('input').addEventListener('change', () => {
                    this.buildFilterList();
                });

                el.querySelector('input').addEventListener('keydown', (event: KeyboardEvent): void => {

                    if (Utils.IsKeyPressed(event, 'Escape')) {
                        this.close();
                    }

                    if (Utils.IsKeyPressed(event, 'Tab')) {
                        let allItems: NodeList = this._list.querySelectorAll('input');

                        if (!event.shiftKey) {
                            if (index >= allItems.length - 1) {
                                this.close();
                            }
                        } else if (index === 0) {
                            this.close();
                        }

                    }

                });

                this._list.appendChild(el);
            });
            return;
        }

        if (!this.data) {
            return;
        }
        this.data.forEach((item: any) => {
            let label: string = this.labelFunction(item);

            let el: Element = Utils.CreateNode(ITEM_TEMPLATE.split('%s').join(label));
            el[DATAFIELD] = item;
            this._list.appendChild(el);
        });
    }

    /**
     * Adds a no result node to the list.
     */
    private createNoResultItem(): void {
        const el: Element = Utils.CreateNode(NO_RESULT_TEMPLATE);
        this._list.appendChild(el);
    }

    /**
     * This is the default filterFunction.
     * @param item
     * @param idx
     */
    private defaultFilter(item: any, idx: number): boolean {
        // Return if matched, this way, we can easily check if there
        // are any matches

        if (this.isCheckboxFilter) {
            item = item.name;
        }
        return item.toString().toLowerCase().indexOf(this.query.toLowerCase()) > -1;
    }

    /**
     * This is the default labelFunction, which will return the item if it's a string,
     * or the labelField if it's an object
     * @param item
     * @param idx
     */
    private defaultLabel(item: any, idx: number): string {
        if (typeof item === 'string') {
            return item;
        } else if (this.labelField && item.hasOwnProperty(this.labelField)) {
            return item[this.labelField].toString();
        }
        // Fallback, we don't want to do this, probably returns [Object object]
        return item.toString();
    }

    /**
     * Filters the list based on the value of the input
     */
    private filterInput(): void {

        if (!this.data) {
            return;
        }
        window.clearTimeout(this._inputTimeoutId);
        this._inputTimeoutId = window.setTimeout(
            () => {
                if (this._prevQuery !== this.query) {
                    this._prevQuery = this.query;
                    let results: Array<string> = this.data.filter((item: any, idx: number, arr: Array<any>) => {

                        let listItem: HTMLElement = this._list.children[idx] as HTMLElement;
                        let
                            link: HTMLElement = listItem.querySelector('.combobox__link') as HTMLElement,
                            match: boolean = this.filterFunction.call(this, item, idx, arr);
                        // Initially hide all items
                        listItem.hidden = true;
                        if (match) {
                            // Show only matches
                            listItem.hidden = false;

                            // When using the default filter, we can highlight the results
                            if (this._highlight && this.query) {
                                link.innerHTML = link.textContent
                                    .split(this.query)
                                    .join(`<span class="combobox__match">${this.query}</span>`);
                            }

                        } else if (this._highlight) {
                            // Reset matches for non matching items (remove <spans>)
                            link.nodeValue = link.textContent;
                        }
                        return match;
                    });

                    results.length ? this.hideNoResultItem() : this.showNoResultItem();

                    if (!this.isOpen) {
                        this.open();
                    }
                }
            },
            this.inputTimeout);

    }

    /**
     * Show the no result item in the dropdown.
     */
    private showNoResultItem(): void {
        const noResultElement: HTMLElement = this._list.querySelector('.combobox__item--no-results') as HTMLElement;
        noResultElement.hidden = false;

        // remove event listeners because we don't want the no result item to be interactable.
        this._list.removeEventListener('click', this);
        this._list.removeEventListener('keyup', this);
        this._list.removeEventListener('keydown', this);
    }

    /**
     * Hide the no result item in the dropdown.
     */
    private hideNoResultItem(): void {
        const noResultElement: HTMLElement = this._list.querySelector('.combobox__item--no-results') as HTMLElement;
        noResultElement.hidden = true;

        // reinstate event listeners.
        if (!this.isCheckboxFilter) {
            this._list.addEventListener('click', this);
        }

        this._list.addEventListener('keyup', this);
        this._list.addEventListener('keydown', this);
    }

    /**
     * Move the focus to the next item in the list
     * @param {number} direction, 1 for forward, -1 for backward
     */
    private focusNextItem(direction: number = 1): void {

        let focusableItem: HTMLElement = document.activeElement as HTMLElement;

        // Check if item belongs to this combobox
        if (
            focusableItem.classList.contains('combobox__item') &&
            Utils.IsDescendant(focusableItem, this.host)) {

            // Are we moving forward or backward
            let method: string = direction === 1 ? 'nextElementSibling' : 'previousElementSibling';

            // Find next focusable element. Focusable elements are elements
            // that are not hidden
            while (focusableItem[method]) {
                focusableItem = focusableItem[method];
                if (!focusableItem.hidden) {
                    // When we found an element, focus it
                    focusableItem.focus();
                    break;
                }
            }

        } else {
            const items: NodeList = this._list.querySelectorAll('.combobox__item');

            for (let i: number = 0, j: number = items.length; i < j; i += 1) {
                const item: HTMLElement = items[i] as HTMLElement;

                if (!item.hasAttribute('hidden')) {
                    item.focus();
                    break;
                }
            }
        }
    }

    /**
     * Called when anything which alters the displayed data has changed
     * (data / labelField)
     */
    private initialize(): void {
        clearTimeout(this._initTimeoutId);

        this._initTimeoutId = window.setTimeout(
            () => {
                this.removeListItems();
                this.createListItems();
                this.createNoResultItem();

                if (this.mode === MODE_AUTOCOMPLETE) {
                    this.filterInput();
                }

            },
            this._initTimeout);

    }

    /**
     * Applies validation to the input. Called when the value of the input has changed
     */
    private onInputinput(): void {
        if (this.data && !this.allowUnknown && this.data.indexOf(this._input.value) === -1) {
            this._input.setCustomValidity(this.validationError);
        } else {
            this._input.setCustomValidity('');
        }
    }

    /**
     * Called when the user uses keyboard nav on the input,
     * prevents body scrolling and event propagation.
     * @param {KeyboardEvent} event
     */
    private onInputkeydown(event: KeyboardEvent): void {
        event.stopPropagation();
    }

    /**
     * Keyboard handler for the input
     * Escape: Close the dropdown
     * ArrowDown / ArrowUp: Move the focus to the list
     * @param {KeyboardEvent} event
     */
    private onInputkeyup(event: KeyboardEvent): void {
        if (Utils.IsKeyPressed(event, 'Escape')) {
            return this.close();
        } else if (Utils.IsKeyPressed(event, 'ArrowDown') || Utils.IsKeyPressed(event, 'ArrowUp')) {
            if (!this.isOpen) {
                this.open();
                const list: HTMLElement = this._list.querySelector('.combobox__item') as HTMLElement;
                if (list) {
                    list.focus();
                }
            } else {
                this.focusNextItem(Utils.IsKeyPressed(event, 'ArrowDown') ? 1 : -1);
            }
        }

        if (this.mode === MODE_FILTER) {
            this._value = null;
            this.filterInput();
        }
    }

    /**
     * Called when the user clicks an item from the list
     * @param {Event} event
     */
    private onListclick(event: Event): void {
        this.setValue(this.findDataField(event.target as HTMLElement));
    }

    private onBodyclick(event: Event): void {

        if (event.target !== this._input &&
            event.target !== this._toggle &&
            event.target !== this._icon &&
            event.target !== this._list) {

            // don't close the flyout if we are checking off an checkbox item.
            if (this.isCheckboxFilter && this.findDataField(event.target as HTMLElement) != null) {
                return;
            }

            this.close();
        }
    }

    /**
     * When the user clicks on the text instead of the list item, we need to find
     * the element with the datafield.
     * We traverse the parents until an element with [DATAFIELD] is found, or no parent is found (should not happen)
     *
     * @param {HTMLElement} el
     * @returns {any}
     */
    private findDataField(el: HTMLElement): any {
        while (el && !el[DATAFIELD]) {
            el = el.parentElement;
        }
        return el && el[DATAFIELD];
    }

    /**
     * Called when the user uses keyboard nav inside the list,
     * prevents body scrolling and event propagation
     * @param {KeyboardEvent} event
     */
    private onListkeydown(event: KeyboardEvent): void {
        event.preventDefault();
        event.stopPropagation();
    }

    /**
     * Keyboard handling for the select list,
     * Escape: Close the dropdown
     * ArrowDown: Focus to the next item in the list
     * ArrowUp: Focus to the previous item in the list
     * Enter: Select the currently focused item
     * @param {KeyboardEvent} event
     */
    private onListkeyup(event: KeyboardEvent): void {
        event.preventDefault();
        if (Utils.IsKeyPressed(event, 'Escape')) {
            this._value = null;
            this.close();
        } else if (Utils.IsKeyPressed(event, 'ArrowDown') || Utils.IsKeyPressed(event, 'ArrowUp')) {
            this.focusNextItem(Utils.IsKeyPressed(event, 'ArrowDown') ? 1 : -1);
        } else if (Utils.IsKeyPressed(event, 'Enter')) {
            let focusedItem: HTMLElement = document.activeElement as HTMLElement;
            if (focusedItem.classList.contains('combobox__item')) {
                let val: any = this.findDataField(focusedItem);
                this.setValue(val);
            }
        }
    }

    /**
     * Called when the user clicks the dropdown button
     * @param {Event} event
     */
    private onToggleclick(event: Event): void {

        if (this.isOpen) {
            this._value = null;
            this.close();
        } else {
            this.open();
        }

    }

    /**
     * Updates the filter list with passed items.
     *      * Adds event listener to the filter to uncheck the
     *      * corresponding checkbox and rebuild the filterlist.
     * @param collection The filters we are adding
     */
    private updateFilterList(collection: Array<any>): void {
        let container: HTMLElement = this.getFilterContainer();
        container.innerHTML = '';
        let index: number;

        for (let i: number = 0; i < collection.length; i++) {
            index = i;
            let filterNode: HTMLElement = Utils.CreateNode('<li tabindex="0">' + collection[i] + '</li>');
            filterNode.id = i.toString();

            filterNode.addEventListener('click', (event: MouseEvent) => this.onFilterLabelClick(event.target));

            filterNode.addEventListener('keyup', (event: KeyboardEvent) => {
                if (Utils.IsKeyPressed(event, 'Enter')) {
                    this.onFilterLabelClick(event.target);
                }
            });
            container.appendChild(filterNode);
        }
    }

    private onFilterLabelClick(element: EventTarget): void {
        // uncheck the corresponding checkbox if we are using
        // a checkbox filter
        if (this.isCheckboxFilter) {

            let filterNode: HTMLElement = element as HTMLElement;

            let controllingCheckbox: HTMLInputElement =
                this._list.querySelectorAll('.input__control:checked')[filterNode.id] as HTMLInputElement;

            controllingCheckbox.checked = false;

            let container: HTMLElement = this.getFilterContainer();

            // rebuild the list
            this.buildFilterList();

            // check focus
            if (container.querySelectorAll('li').length === 0) {
                // focus list
                this._input.focus();
            } else {
                // focus previous element
                let previousSibling: number = parseInt(filterNode.id) > 0 ? parseInt(filterNode.id) - 1 : 0;
                let focusableItem: HTMLElement = container.querySelectorAll('li')[previousSibling] as HTMLElement;
                focusableItem.focus();
            }
        }

    }

    /**
     * Adds a list--filter to the combobox for showing the
     * currently checked checkboxes.
     * If the element does not exist within the host, a new
     * element will be inserted.
     */
    private getFilterContainer(): HTMLElement {

        // if no element is referenced
        if (this._filterContainer === null) {

            // does a list--filter already exist on the host?
            this._filterContainer = this.host.querySelector('.list--filter') as HTMLElement;
            if (this._filterContainer === null) {
                // create a new list--filter and add it before the input
                this._filterContainer = Utils.CreateNode(`<ul class="list list--filter list--filter-inline list--filter-closable" />`);
                this.host.insertBefore(this._filterContainer, this.host.querySelector('.combobox__input'));
            }
        }

        return this._filterContainer;
    }

    /**
     * Prepares the filterlist by passing all checked boxes to the updateFilterList
     */
    private buildFilterList(): void {
        this._value = [];
        let checkedItems: NodeListOf<Element> = this._list.querySelectorAll('.input__control:checked');
        for (let i: number = 0; i < checkedItems.length; i++) {
            this._value.push(checkedItems[i].parentElement.querySelector('.input__label').textContent);
        }
        this.updateFilterList(this._value);
    }

    /**
     * Set the value of the input based on a list item
     * Dispatches an 'combobox-select' event
     * @param {string} value
     */
    private setValue(value: any): void {
        let event: any = document.createEvent('CustomEvent');
        event.initEvent('combobox-select', true, true);
        event.data = value;

        if (!this.isCheckboxFilter && this.host.dispatchEvent(event)) {
            this.value = value;
            this.close();
        }
    }

    /**
     * Scaffolding, add correct classes & elements
     */
    private setup(): void {
        if (!this.host.querySelector('.combobox__input')) {
            throw new Error('Host element should contain a text input');
        }

        if (!this.host.classList.contains('combobox')) {
            this.host.classList.add('combobox');
        }

        // Check if the icon is present
        if (!this.host.querySelector('.combobox__icon')) {
            this._icon = Utils.CreateNode(`<i class="combobox__icon icon icon-magnifier" role="presentation"></i>`) as HTMLElement;
            this.host.appendChild(this._icon);
        }

        // Check if the button is present
        this._toggle = this.host.querySelector('.combobox__toggle');

        if (!this._toggle) {
            // No button, create it
            this._toggle = Utils.CreateNode(`<button type="button" class="combobox__toggle"></button>`);
            this.host.appendChild(this._toggle);
        }

        // Check if the autocomplete list is present
        if (!this.host.querySelector('.combobox__autocomplete')) {
            let list: Element = Utils.CreateNode(
                `<div class="combobox__autocomplete">
                     <div class="combobox__list-wrapper">
                         <ul class="combobox__list" tabindex="0" hidden>
                         </ul>
                     </div>
                 </div>`);

            this.host.appendChild(list);

        }

        // Store a reference to the <ul>
        this._list = this.host.querySelector('.combobox__list') as HTMLElement;

        // When we allow multiple, we create compact checkboxes from the element children
        if (this.isCheckboxFilter) {

            // add the modifier
            this._list.classList.add('combobox__list--multiple');

            this._list.setAttribute('tabindex', '-1');

            // get reference to the current input
            let checkboxValues: HTMLInputElement = this.host.querySelector('.combobox__input') as HTMLInputElement;

            // use the _data for storing the items
            this._data = [];
            for (let i: number = 0; i < checkboxValues.children.length; i++) {
                this._data.push({ name: checkboxValues.children[i].textContent, value: '' });
            }

            // swap the current element with an original input and copy the classes
            let inputElement: Element = Utils.CreateNode('<input />');
            inputElement.className = checkboxValues.className;
            this.host.replaceChild(inputElement, checkboxValues);

            // create the items in the list
            this.createListItems();

            // create no result item in the  list
            this.createNoResultItem();
        }

        // Store a reference to the <input>
        this._input = this.host.querySelector('.combobox__input') as HTMLInputElement;
    }

    private setupListeners(): void {

        // Add a click listener to the button
        this._toggle.addEventListener('click', this);

        this._input.addEventListener('keyup', this);
        this._input.addEventListener('keydown', this);
        this._input.addEventListener('input', this);

        if (!this.isCheckboxFilter) {
            this._list.addEventListener('click', this);
        }

        this._list.addEventListener('keyup', this);
        this._list.addEventListener('keydown', this);

        document.body.addEventListener('click', this);
    }

    /**
     * Removes all the listitems from the suggest list
     */
    private removeListItems(): void {
        // list.remove
        while (this._list.lastChild) {
            this._list.removeChild(this._list.lastChild);
        }
    }
}
