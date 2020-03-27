import {Utils} from '../../core/utils';
const RESIZE_THROTTLE: number = 200;
/**
 * All screens lower than this will always show a hamburger
 * @type {number}
 */
const MOBILE_WIDTH: number = 544;
/**
 * @class AutoResizeNavigation
 * Creates an navigation component that automatically puts items in a menu to fit in the available width
 *
 * Example:
 *
 * <code>
 *   <div>
 *      <nav class="nav nav--simple nav--donkergeel">
 *          <div class="container">
 *               <div class="nav-autoresize">
 *                   <ul class="nav">
 *                       <li class="nav__item">
 *                            <a class="nav__link" href="#">Core</a>
 *                       </li>
 *                       <li class="nav__item">
 *                            <a class="nav__link nav__link--active" href="#">Componenten</a>
 *                       </li>
 *                       <li class="nav__item">
 *                           <a class="nav__link" href="#">Changelog</a>
 *                       </li>
 *                    </ul>
 *               </div>
 *           </div>
 *       </nav>
 *    </div>
 *    <script>
 *      // Load nav component
 *      System.import('/uno/components/navigation/navigation.js').then(function (module) {
 *      var navs = document.querySelectorAll('.nav-autoresize > ul');
 *      // Initialize all navs
 *      for (let i = 0; i < navs.length; i++) {
 *          new module.AutoResizeNavigation(navs.item(i));
 *      }
 *      });
 *    </script>
 * </code>
 */

export class AutoResizeNavigation {

    private _navWidth: number = 0;
    private _moreNav: HTMLElement;
    private _resizeTimeout: number;
    private _ul: Element;

    private _navClickListener: EventListenerObject;
    private resizeListener: EventListenerObject;
    private windowClickListener: EventListenerObject;

    constructor(private host: Element) {
        if (!host) {
            throw new Error('host element not defined');
        }
        if (!host.classList.contains('top-nav-autoresize')) {
            throw new Error('host element should have class top-nav-autoresize');
        }
        if (!Utils.FindParentContainingClass(host, 'container', document.body)) {
            throw new Error('Autoresize navigation should be in parent with class container');
        }
        if (!host.querySelector('ul.nav')) {
            throw new Error('host element should have child ul with class nav');
        }
        this._ul = host.querySelector('ul.nav');

        this._setup();
    }

    /**
     * Creates a 'more' button, which can hold all clones
     * @private
     */
    private _createMoreNav(): void {
        this._moreNav = document.createElement('li');
        this._moreNav.classList.add('nav__item');
        this._moreNav.classList.add('nav__item--more');
        // Put the text in a separate span to fix underlining / positioning
        // of arrow
        this._moreNav.innerHTML = `
            <a href="#" class="nav__link nav__link--more">Meer</a>
            <ul class="nav__more"></ul>`;

        this._createClones();
        this._ul.appendChild(this._moreNav);

        this._navClickListener = this._navClassToggle(this._moreNav, 'nav__item--more-open').bind(this);

        this._moreNav.querySelector('a').addEventListener('click', this._navClickListener);
    }

    private _navClassToggle(element: HTMLElement, cssClass: string): Function {
        return (event: Event) => {
            event.preventDefault();
            event.stopPropagation();
            element.classList.toggle(cssClass);
        };
    }

    private _onWindowClick():void {
        this._moreNav.classList.remove('nav__item--more-open');
    }

    private _setup(): void {
        this.resizeListener = this._onScreenResize.bind(this);
        this.windowClickListener = this._onWindowClick.bind(this);
        window.addEventListener('resize', this.resizeListener);
        window.addEventListener('click', this.windowClickListener);

        this._createMoreNav();
        this._storeWidths();
        this._setFlexClasses();

        // Add negative tabindex to all disabled navs to prevent keyboard nav
        let disabled: NodeList = this._ul.querySelectorAll('.nav__item--disabled');

        for (let i: number = 0; i < disabled.length; i++) {
            (disabled.item(i) as Element).querySelector('a').setAttribute('tabindex', '-1');
        }

        this._rearrange();
    }

    private _setFlexClasses(): void {
        let el: Element = this.host;
        while (el.nextElementSibling) {
            el = el.nextElementSibling;
            el.classList.add('autoresize__sibling');
        }
        el = this.host;
        while (el.previousElementSibling) {
            el = el.previousElementSibling;
            el.classList.add('autoresize__sibling');
        }
    }

    private _rearrange(): void {

        this.host.classList.add('top-nav-autoresize--setup');

        // Get the total available width
        let availableWidth: number = parseInt(getComputedStyle(this._ul).width);

        this.host.classList.remove('top-nav-autoresize--setup');

        if (document.body.offsetWidth <= MOBILE_WIDTH) {
            // Set available width to 0, so all items are forced inside
            // the hamburger
            availableWidth = 0;
        }

        // Get the width of all navs combined
        let navsWidth: number = this._navWidth;
        // Get all original navs
        let navs: NodeList = this._ul.querySelectorAll('.nav__item:not(.nav__item--clone)');

        // Get all the clones
        let clones: NodeList = this._ul.querySelectorAll('.nav__item--clone');

        let numNavs: number = navs.length - 1;

        (navs.item(0) as HTMLElement).style.maxWidth = '';
        // Make all original navs visible, hide all clones
        while (--numNavs > 0) {
            (navs.item(numNavs) as HTMLElement).classList.remove('nav__item--hidden');
            (clones.item(numNavs) as HTMLElement).classList.add('nav__item--hidden');
        }

        numNavs = navs.length - 1;

        // Iterate over all navs and move them one by one into the more list
        // Until it fits inside the available width or there are no more
        // navs left.
        let numHidden: number = 0;
        while (navsWidth > availableWidth && --numNavs > 0) {
            let nav: HTMLElement = (navs.item(numNavs) as HTMLElement),
                clone: HTMLElement = (clones.item(numNavs) as HTMLElement);

            nav.classList.add('nav__item--hidden');
            clone.classList.remove('nav__item--hidden');
            numHidden++;
            navsWidth -= parseInt(nav.getAttribute('data-width'));
        }

        if (numNavs === 0) {
            // also put last item in more menu
            (this._moreNav.querySelector('.nav__link--more') as HTMLElement).innerHTML = 'MENU';
            (navs.item(0) as HTMLElement).classList.add('nav__item--hidden');
            (clones.item(0) as HTMLElement).classList.remove('nav__item--hidden');
            (this._moreNav as HTMLElement).classList.add('nav__item--minified');
        } else {
            (this._moreNav.querySelector('.nav__link--more') as HTMLElement).innerHTML = 'Meer';
            (navs.item(0) as HTMLElement).classList.remove('nav__item--hidden');
            (clones.item(0) as HTMLElement).classList.add('nav__item--hidden');
            (this._moreNav as HTMLElement).classList.remove('nav__item--minified');
        }

        // IE10 does not support toggle(class, force) syntax
        if (this._ul.querySelector('.nav__item--clone:not(.nav__item--hidden)') === null) {
            this._moreNav.classList.add('nav__item--hidden');
        } else {
            this._moreNav.classList.remove('nav__item--hidden');
        }
    }

    /**
     * Creates a clone of every nav and moves it inside the more button
     * @private
     */
    private _createClones(): void {
        let moreList: Element = this._moreNav.querySelector('ul');
        let navs: NodeList = this._ul.querySelectorAll('.nav__item');
        for (let i: number = 0; i < navs.length; i++) {
            // Copy each nav item and add a --clone & --hidden modifier
            let clone: Element = navs.item(i).cloneNode(true) as Element;
            clone.removeAttribute('id');
            clone.classList.add('nav__item--clone');
            clone.classList.add('nav__item--hidden');
            moreList.appendChild(clone);
        }
    }

    private _onScreenResize(): void {
        clearTimeout(this._resizeTimeout);
        this._resizeTimeout = window.setTimeout(
            this._rearrange.bind(this),
            RESIZE_THROTTLE
        );
    }

    /**
     * Stores the width of each nav and calculates the total width
     * @private
     */
    private _storeWidths(): void {
        this.host.classList.add('top-nav-autoresize--setup');

        let navs: NodeList = this._ul.querySelectorAll('.nav__item:not(.nav__item--clone)');

        for (let i: number = 0; i < navs.length; i++) {
            let nav: HTMLElement = navs.item(i) as HTMLElement;
            let width: number = Math.ceil(parseFloat(getComputedStyle(nav).width));
            width += Math.ceil(parseFloat(getComputedStyle(nav).marginLeft));
            width += Math.ceil(parseFloat(getComputedStyle(nav).marginRight));
            this._navWidth += width;
            nav.setAttribute('data-width', width.toString());
        }

        this.host.classList.remove('top-nav-autoresize--setup');
    }

    /**
     * Removes references for correct GC
     */
    destroy(): void {
        window.removeEventListener('resize', this.resizeListener);
        window.removeEventListener('click', this.windowClickListener);
        this._moreNav.querySelector('a').removeEventListener('click', this._navClickListener);
    }
}

export class SubMenuNavigation {

    private _navClickListener: EventListenerObject;
    private windowClickListener: EventListenerObject;

    constructor(private host: Element) {

        if (!host) {
            throw new Error('host element not defined');
        }
        if (!host.classList.contains('nav--submenu')) {
            throw new Error('host element should have class nav--submenu');
        }
        if (!Utils.FindParentContainingClass(host, 'container', document.body)) {
            throw new Error('Submenu navigation should be in parent with class container');
        }
        if (!host.querySelector('li.nav__item--parent')) {
            throw new Error('host element should have child li with class nav__item--parent');
        }

        let parentItems: NodeList = host.querySelectorAll('.nav__item--parent');

        for (let k: number = 0; k < parentItems.length; k++) {
            let parentItem:HTMLElement = parentItems.item(k) as HTMLElement;
            if (!parentItem.querySelector('a.nav__link--parent')) {
                throw new Error('host element should have child a with class nav__link--parent');
            }
            if (!parentItem.querySelector('ul.nav__submenu')) {
                throw new Error('host element should have child ul with class nav__submenu');
            }
            this._navClickListener = this._navClassToggle(parentItem, 'nav__item--parent-open').bind(this);
            parentItem.querySelector('a').addEventListener('click', this._navClickListener);

            this.windowClickListener = this._navClassRemove(parentItem, 'nav__item--parent-open').bind(this);
            window.addEventListener('click', this.windowClickListener);
        }
    }

    private _navClassToggle(element: HTMLElement, cssClass: string): Function {
        return (event: Event) => {
            event.preventDefault();
            event.stopPropagation();
            element.classList.toggle(cssClass);
        };
    }

    private _navClassRemove(element: HTMLElement, cssClass: string): Function {
        return () => {
            element.classList.remove(cssClass);
        };
    }

    /**
     * Removes references for correct GC
     */
    destroy(): void {
        let parentItems: NodeList = this.host.querySelectorAll('.nav__item--parent');

        for (let k: number = 0; k < parentItems.length; k++) {
            let parentItem: HTMLElement = parentItems.item(k) as HTMLElement;
            parentItem.querySelector('a').removeEventListener('click', this._navClickListener);
        }
        window.removeEventListener('click', this.windowClickListener);
    }
}
