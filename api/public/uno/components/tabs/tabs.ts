// Timeout to wait before the actual resize logic is executed
import {Utils} from './../../core/utils';
import {IListenerReference} from '../../core/interfaces/IListenerReference';

const RESIZE_THROTTLE: number = 200;

/**
 * @class Tabs
 * Creates an interactive tabs component
 *
 * Example:
 * <code>
 *     <ul class="tab" role="tablist">
 *         <li id="tab1" role="tab" aria-controls="panel1" class="tab__tab"><a href="#panel1">Tab 1</a></li>
 *         <li id="tab2" role="tab" aria-controls="panel2" class="tab__tab"><a href="#panel2">Tab 2</a></li>
 *         <li id="tab3" role="tab" aria-controls="panel3" class="tab__tab"><a href="#panel3">Tab 3</a></li>
 *     </ul>
 *
 *     <div class="content-background tab__content">
 *         <div class="content tab__pane" id="panel1" role="tabpanel" aria-labelledby="tab1">
 *             <p>Tab 3</p>
 *         </div>
 *         <div class="content tab__pane" id="panel2" role="tabpanel" aria-labelledby="tab2">
 *             <p>Tab 2</p>
 *         </div>
 *         <div class="content tab__pane" id="panel3" role="tabpanel" aria-labelledby="tab3">
 *             <p>Tab 3</p>
 *         </div>
 *      </div>
 *      <script>
 *          // Load tabs component
 *          System.import('/uno/components/tabs/tabs.js').then(function (module) {
 *             // Select all <details> elements on the page
 *              var tabs = document.querySelectorAll('.tab');
 *              // Initialize all tabs
 *              for (let i = 0; i < tabs.length; i++) {
 *                 new module.Tabs(tabs.item(i));
 *              }
 *          });
 *      </script>
 * </code>
 */

export class Tabs implements EventListenerObject {

    private _tabs: NodeList;
    private _tabsWidth: number = 0;
    private _moreTab: HTMLElement;
    private _resizeTimeout: number;

    get activeTabIndex(): number {
        let index: number;

        for (let i: number = 0; i < this._tabs.length; i++) {
            if ((this._tabs.item(i) as HTMLElement).parentElement.classList.contains('tab__tab--active')) {
                index = i;
                break;
            }
        }

        return index;
    }

    /**
     * Deactivates all the panes of the given pane container
     * @param parent
     */
    public static deactivatePanes(parent: Element): void {

        let elements: NodeList = parent.querySelectorAll('.tab__pane');

        for (let i:number = 0; i < elements.length; i += 1) {
            (elements.item(i) as Element).classList.remove('tab__pane--active');
        }
    }

    /**
     * Get the tab(s) which is/are currently active, there can be more than
     * one, since a cloned tab can have active state as well as the
     * original one.
     */
    public static deactivateTabs(host: Element): void {
        let activeTabs: NodeList = host.querySelectorAll('.tab__tab--active');

        if (activeTabs.length > 0) {
            // Deactivate all currently active tabs
            for (let i:number = 0; i < activeTabs.length; i++) {
                let activeTab: HTMLElement = activeTabs.item(i) as HTMLElement;
                activeTab.querySelector('a').setAttribute('tabindex', '-1');
                activeTab.classList.remove('tab__tab--active');
                activeTab.setAttribute('aria-selected', 'false');
            }
        }
    }

    /**
     * Gets the target pane based on the specified href of the tab link
     * @param tab
     * @returns {HTMLElement}
     */
    public static getTargetPane(tab: Element): Element {
        let target: string = tab.getAttribute('href').substr(1);

        return document.getElementById(target);
    }

    constructor(private host: Element) {
        if (!host) {
            throw new Error('Host element not supplied');
        }

        this._tabs = host.querySelectorAll('.tab__tab a');

        if (this._tabs.length === 0) {
            throw new Error('No tabs found in host element');
        }

        this.setup();
    }

    /**
     * Removes references for correct GC
     */
    destroy(): void {
        window.removeEventListener('resize', this);

        this._tabs = this.host.querySelectorAll('.tab__tab a');

        for (let i: number = 0; i < this._tabs.length; i++) {
            this._tabs.item(i).removeEventListener('click', this);
        }

    }

    disableTab(idx: number): void {
        if (idx === this.activeTabIndex) {
            throw new Error(`Cannot disable active tab since it's active`);
        } else {
            this.toggleEnabled(idx, false);
        }
    }

    enableTab(idx: number): void {
        this.toggleEnabled(idx, true);
    }

    /**
     * @private
     * @param {Event} event
     */
    handleEvent(event: Event): void {
        if (event.currentTarget === window && event.type === 'resize') {
            this.onScreenResize();
        } else if ((event.currentTarget as HTMLElement).parentElement.classList.contains('tab__tab')) {
            this.onTabClick(event as MouseEvent);
        }
    }

    openTab(idx: number): void {
        // Length -1, last tab is the 'more' tab, which should not be included
        if (idx >= 0 && idx < this._tabs.length - 1) {
            this.setActiveTab(this._tabs.item(idx) as Element);
        } else {
            throw new Error(`Tab index ${idx} is out of bounds`);
        }
    }

    /**
     * Checks if the given tab is not disabled and is not the 'more' button
     * @param tab
     * @return {boolean}
     * @private
     */
    private canBeActivated(tab: Element): boolean {

        // 'more-tab' cannot be activated
        if (tab.classList.contains('tab__tab--more')) {
            // Toggle a classlist for keyboard navigation
            // mimics hover state
            tab.classList.toggle('tab__tab--more-open');
            return false;
        } else {
            this._moreTab.classList.remove('tab__tab--more-open');
        }

        // Check if we're allowed to switch to this tab
        return !tab.classList.contains('tab__tab--disabled');
    }

    /**
     * Creates a clone of every tab and moves it inside the more button
     * @private
     */
    private createClones(): void {
        let moreList: Element = this._moreTab.querySelector('ul');
        let tabs: NodeList = this.host.querySelectorAll('.tab__tab');
        for (let i: number = 0; i < tabs.length; i++) {
            // Copy each tab and add a --clone & --hidden modifier
            let clone: Element = tabs.item(i).cloneNode(true) as Element;
            clone.removeAttribute('id');
            clone.classList.add('tab__tab--clone');
            clone.classList.add('tab__tab--hidden');
            moreList.appendChild(clone);
        }
    }

    /**
     * Creates a 'more' button, which can hold all clones
     * @private
     */
    private createMoreTab(): void {
        this._moreTab = Utils.CreateNode(
            `<li class="tab__tab tab__tab--more">
                <a href=""><span class="tab__more-link">Meer</span></a>
                <ul class="tab__more"></ul>
            </li>`);

        this.createClones();
        this.host.appendChild(this._moreTab);
    }

    private onScreenResize(): void {
        clearTimeout(this._resizeTimeout);

        this._resizeTimeout = window.setTimeout(
            this.rearrange.bind(this),
            RESIZE_THROTTLE
        );
    }

    /**
     * Event handler for the tab clicks
     * @param event
     * @private
     */
    private onTabClick(event: MouseEvent): void {
        // Do not scroll down
        event.preventDefault();
        this.setActiveTab(event.currentTarget as Element);
    }

    private keyboardSwitchTabs(tabElement: KeyboardEvent): void {
        if (tabElement.key === 'ArrowLeft') {
           // let previousIndex: number = this.activeTabIndex - 1;
            let previousElement: Element = this.getPreviousTab(); // this._tabs.item(previousIndex) as Element;

            if (previousElement) {
                this.setActiveTab(previousElement);
            }
        }
        else if (tabElement.key === 'ArrowRight') {
            // let nextIndex: number = this.activeTabIndex + 1;
            let nextElement: Element = this.getNextTab(); // _tabs.item(nextIndex) as Element;
             // let tabbableList: Array = Array.from(this._tabs).filter((item: Element) => item.tabIndex >= 0);
            if (nextElement) {
                this.setActiveTab(nextElement);
            }
        }

    }

    private rearrange(): void {
        // Get the total available with
        let availableWidth: number = parseInt(getComputedStyle(this.host).width);
        // Get the with of all tabs combined
        let tabsWidth: number = this._tabsWidth;
        // Get all original tabs
        let tabs: NodeList = this.host.querySelectorAll('.tab__tab:not(.tab__tab--clone)');

        // Get all the clones
        let clones: NodeList = this.host.querySelectorAll('.tab__tab--clone');

        let numTabs: number = tabs.length - 1;

        (tabs.item(0) as HTMLElement).style.maxWidth = '';
        // Make all original tabs visible, hide all clones
        while (--numTabs > 0) {
            (tabs.item(numTabs) as HTMLElement).classList.remove('tab__tab--hidden');
            (clones.item(numTabs) as HTMLElement).classList.add('tab__tab--hidden');
        }

        numTabs = tabs.length - 1;

        // Iterate over all tabs and move them one by one into the more list
        // Until it fits inside the available width or there are no more
        // tabs left.
        while (tabsWidth > availableWidth && --numTabs > 0) {
            let tab: HTMLElement = (tabs.item(numTabs) as HTMLElement),
                clone: HTMLElement = (clones.item(numTabs) as HTMLElement);

            tab.classList.add('tab__tab--hidden');
            clone.classList.remove('tab__tab--hidden');

            tabsWidth -= parseInt(tab.getAttribute('data-width'));
        }

        if (numTabs === 0) {
            let maxWidth: number = availableWidth - parseInt(this._moreTab.getAttribute('data-width'));
            (tabs.item(0) as HTMLElement).style.maxWidth = maxWidth + 'px';
        }

        // IE10 does not support toggle(class, force) syntax
        if (this.host.querySelector('.tab__tab--clone:not(.tab__tab--hidden)') === null) {
            this._moreTab.classList.add('tab__tab--hidden');
        } else {
            this._moreTab.classList.remove('tab__tab--hidden');
        }

    }

    private setup(): void {
        window.addEventListener('resize', this);

        this.createMoreTab();
        this.storeWidths();

        this._tabs = this.host.querySelectorAll('.tab__tab a');

        let disabled: NodeList = this.host.querySelectorAll('.tab__tab--disabled');

        for (let i: number = 0; i < disabled.length; i++) {
            (disabled.item(i) as Element).setAttribute('disabled', '');
        }

        let activeTab: Element = this.host.querySelector('.tab__tab--active a');

        for (let i: number = 0; i < this._tabs.length; i++) {
            this._tabs.item(i).addEventListener('click', this);
            this._tabs.item(i).addEventListener('keyup', this.keyboardSwitchTabs.bind(this));
            (this._tabs.item(i) as Element).setAttribute('tabindex', '-1');
        }

        // If no active tab is set, set first tab as active
        this.setActiveTab(activeTab || this._tabs.item(0) as Element);

        this.rearrange();
    }

    /**
     * Returns the requested tab element when not hidden, otherwise returns the clone of the tab element.
     * @param tabs The NodeList with tab elements.
     * @param clones The NodeList with clones of the tab elements.
     * @param index the index of the requested Tab element.
     * @private
     */
    private getTabElement(tabs: NodeList, clones: NodeList, index: number): Element {
        const isTabHidden: Boolean = (tabs[index] as HTMLElement).classList.contains('tab__tab--hidden');
        const tab: Node = isTabHidden ? clones[index] : tabs[index];
        return tab.firstChild as Element;
    }

     /**
      * Returns the first active tab element after the current tab element.
      * @private
      */
    private getNextTab(): Element {
        const tabs: NodeList = this.host.querySelectorAll('.tab__tab:not(.tab__tab--clone):not(.tab__tab--more)');
        const clones: NodeList = this.host.querySelectorAll('.tab__tab--clone');
        const nextTab: number = this.activeTabIndex + 1;
        const tabIndex: number = (nextTab < tabs.length) ? nextTab : 0;

        for (let i: number = tabIndex; i < tabs.length; i += 1) {
            if (this.canBeActivated(Utils.FindParentContainingClass(tabs.item(i) as Element, 'tab__tab', this.host))){
                return this.getTabElement(tabs, clones, i);
            }
        }

        return null;
    }

     /**
      * Returns the first active tab element before the current tab element.
      * @private
      */
    private getPreviousTab(): Element {
        const tabs: NodeList = this.host.querySelectorAll('.tab__tab:not(.tab__tab--clone )');
        const clones: NodeList = this.host.querySelectorAll('.tab__tab--clone');
        const prevTab: number = this.activeTabIndex - 1;
        const tabIndex: number = (prevTab >= 0) ? prevTab : tabs.length - 1;

        for (let i: number = tabIndex; i >= 0; i -= 1) {
            if (this.canBeActivated(Utils.FindParentContainingClass(tabs.item(i) as Element, 'tab__tab', this.host))){
                return this.getTabElement(tabs, clones, i);
            }
        }

        return null;
    }

    /**
     * Sets the tab__pane active which is linked to the given element. The
     * tab__pane should have the id as specified in the url of the tab
     * @param el The a-tag in the .tab__tab element
     * @private
     */
    private setActiveTab(el: Element): void {
        // Select the node with ID target
        let pane: Element = Tabs.getTargetPane(el),

            // Get the clicked tab
            tab: Element = Utils.FindParentContainingClass(el, 'tab__tab', this.host);

        if (!this.canBeActivated(tab)) {
            return;
        }

        Tabs.deactivateTabs(this.host);

        // Remove active states from panes
        Tabs.deactivatePanes(Utils.FindParentContainingClass(pane.parentElement, 'tab__content', this.host));
        // Set all tabs with the same target as active
        let target: String = el.getAttribute('href'),
            tabs: NodeList = this.host.querySelectorAll(`a[href="${target}"]`);

        for (let i: number = 0; i < tabs.length; i++) {
            tab = Utils.FindParentContainingClass(tabs.item(i) as Element, 'tab__tab', this.host);
            (tabs.item(i) as Element).removeAttribute('tabindex');

            // Set active state to current tab / pane
            tab.classList.add('tab__tab--active');
            tab.setAttribute('aria-selected', 'true');

            if ((tab as HTMLElement).parentElement.classList.contains('tab__more')
            && !(tab as HTMLElement).classList.contains('tab__tab--hidden')) {
                this._moreTab.classList.add('tab__tab--more-open');
            }
        }

        (el as HTMLElement).focus();

        pane.classList.add('tab__pane--active');
    }

    /**
     * Stores the width of each tab and calculates the total width
     * @private
     */
    private storeWidths(): void {
        // Switch to setup mode so all tabs are placed next to each other
        this.host.classList.add('tab--setup');

        let tabs: NodeList = this.host.querySelectorAll('.tab__tab:not(.tab__tab--clone)');

        for (let i: number = 0; i < tabs.length; i++) {
            let tab: HTMLElement = tabs.item(i) as HTMLElement;
            let width: number = Math.ceil(parseFloat(getComputedStyle(tab).width));
            width += Math.ceil(parseFloat(getComputedStyle(tab).marginLeft));
            width += Math.ceil(parseFloat(getComputedStyle(tab).marginRight));
            this._tabsWidth += width;
            tab.setAttribute('data-width', width.toString());
        }

        this.host.classList.remove('tab--setup');
    }

    private toggleEnabled(idx: number, enabled: boolean): void {
        if (idx >= 0 && idx < this._tabs.length - 1) {
            let tabs: NodeList = this.host.querySelectorAll('.tab__tab:not(.tab__tab--clone)'),
                clones: NodeList = this.host.querySelectorAll('.tab__tab--clone'),
                classListAction: string = enabled ? 'remove' : 'add';

            if (enabled) {
                (tabs.item(idx) as HTMLElement).removeAttribute('disabled');
                (clones.item(idx) as HTMLElement).removeAttribute('disabled');
            } else {
                (tabs.item(idx) as HTMLElement).setAttribute('disabled', '');
                (clones.item(idx) as HTMLElement).setAttribute('disabled', '');
            }
            (tabs.item(idx) as HTMLElement).classList[classListAction]('tab__tab--disabled');
            (tabs.item(idx) as HTMLElement).querySelector('a').setAttribute('tabindex', enabled ? (0).toString() : (-1).toString());

            (clones.item(idx) as HTMLElement).classList[classListAction]('tab__tab--disabled');
            (clones.item(idx) as HTMLElement).querySelector('a').setAttribute('tabindex', enabled ? (0).toString() : (-1).toString());
        } else {
            throw new Error(`Tab index ${idx} is out of bounds`);
        }

    }
}
