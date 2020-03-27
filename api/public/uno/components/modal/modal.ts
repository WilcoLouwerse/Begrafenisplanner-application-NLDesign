import {Utils} from './../../core/utils';
const RESIZE_TIMEOUT:number = 200;

/**
 * @class Modal
 * Creates a modal window which can be opened / closed using javascript
 * Example:
 * <code>
 *     <div class="modal" role="dialog">
 *          <section class="modal__content">
 *              <header class="modal__title">
 *                  <a href="#" class="modal__close-button">
 *                      <i class="icon icon-cross"></i><span>Sluit</span>
 *                  </a>
 *                  <h1>Titel van modal</h1>
 *              </header>
 *
 *              <div class="modal__body">
 *                  <p>Ut quis congue sapien. Proin maximus augue molestie.</p>
 *              </div>
 *
 *              <footer class="modal__footer">
 *                  <button class="btn btn--primary">Bevestig</button>
 *              </footer>
 *          </section>
 *      </div>
 *      <script>
 *          System.import('/uno/components/modal/modal.js').then(function (module) {
 *              var el = document.querySelector('.modal'),
 *                  modal = new module.Modal(el);
 *
 *              modal.open();
 *          });
 *      </script>
 * </code>
 */
export class Modal{

    private resizeTimeout:number;
    private previousFocus:Element;
    private focusableElementsString:string = `
        a[href],
        area[href],
        input:not([disabled]),
        select:not([disabled]),
        textarea:not([disabled]),
        button:not([disabled]),
        iframe,
        object,
        embed,
        *[tabindex],
        *[contenteditable]`;

    private closeListener:EventListenerObject;
    private keyDownListener:EventListenerObject;
    private resizeListener:EventListenerObject;

    constructor(private element:HTMLElement) {
        this.closeListener = this.onClose.bind(this);
        this.keyDownListener = this.onKeyDown.bind(this);
        this.resizeListener = this.onResize.bind(this);
        element.setAttribute('tabindex', '-1');
        if (element.querySelector('.modal__close-button')) {
            element.querySelector('.modal__close-button').addEventListener('click', this.closeListener);
        }

        element.addEventListener('keydown', this.keyDownListener);

        window.addEventListener('resize', this.resizeListener);

        this.setAria();
    }

    private  onKeyDown(event:KeyboardEvent):void {
        // if escape pressed
        if (Utils.IsKeyPressed(event, 'Escape')) {
            event.preventDefault();
            this.close();
        } else
        // if tab or shift-tab pressed
        if (Utils.IsKeyPressed(event, 'Tab')) {
            // get list of focusable items
            let focusableItems:Array<HTMLElement>;
            // convert Nodelist to Array using array prototype
            focusableItems = Array.prototype.slice.call(this.element.querySelectorAll(this.focusableElementsString), 0);

            // sort according to tabindex
            focusableItems.sort(function(a:HTMLElement, b:HTMLElement):number {
                let aInd:String = a.getAttribute('tabindex');
                let bInd:String = b.getAttribute('tabindex');
                if (aInd > bInd) {
                    return 1;
                } else if (aInd < bInd) {
                    return -1;
                }
                return 0;
            });

            // get currently focused item
            let focusedItem:HTMLElement;
            focusedItem = document.querySelector(':focus') as HTMLElement;

            // get the number of focusable items
            let numberOfFocusableItems:number;
            numberOfFocusableItems = focusableItems.length;

            // get the index of the currently focused item but make sure to always return >= 0
            let focusedItemIndex:number = Math.max(Array.prototype.indexOf.call(focusableItems, focusedItem), 0);

            if (event.shiftKey) {
                // back tab
                // if focused on first item and user preses back-tab, go to the last focusable item
                if (focusedItemIndex === 0) {
                    focusableItems[numberOfFocusableItems - 1].focus();
                    event.preventDefault();
                }else {
                    focusableItems[focusedItemIndex - 1].focus();
                    event.preventDefault();
                }

            } else {
                // forward tab
                // if focused on the last item and user preses tab, go to the first focusable item
                if (focusedItemIndex === numberOfFocusableItems - 1) {
                    focusableItems[0].focus();
                    event.preventDefault();
                }else{
                    focusableItems[focusedItemIndex + 1].focus();
                    event.preventDefault();
                }
            }
        }

    }

    /**
     * Opens the modal, sets correct aria-attributes
     */
    public open():void {
        this.previousFocus = document.activeElement;

        this.element.classList.add('modal--open');
        this.resize();
        this.setFocus();
    }

    /**
     * Closes the modal, restores focus
     */
    public close():void {
        this.element.classList.remove('modal--open');

        if (this.previousFocus) {
            // Restore focus
            (this.previousFocus as HTMLElement).focus();
        }
    }

    /**
     * Performs housekeeping for correct garbage collection
     */
    public destroy():void {

        clearTimeout(this.resizeTimeout);
        if (this.element.querySelector('.modal__close-button')) {
            this.element.querySelector('.modal__close-button').removeEventListener('click', this.closeListener);
        }
        this.element.removeEventListener('keydown', this.keyDownListener);

        window.removeEventListener('resize', this.resizeListener);

    }

    /**
     * Resizes the body of the modal to the maximum available width. This is only
     * done when the body height exceeds the height of the modal, which only
     * occurs in IE <= 11.
     */
    public resize():void {
        let body:HTMLElement = this.element.querySelector('.modal__body') as HTMLElement,
            title:Element = this.element.querySelector('.modal__title'),
            footer:Element = this.element.querySelector('.modal__footer'),
            styles:CSSStyleDeclaration;

        body.style.maxHeight = '100%';

        styles = getComputedStyle(this.element.querySelector('.modal__content'));
        let modalHeight:number = parseInt(styles.height),
            bodyHeight:number = parseInt(getComputedStyle(body).height);

        // This is only needed for IE, which doesn't follow the flexbox spec
        // correctly and will not resize the body container.
        if (bodyHeight > modalHeight) {
            // IE
            let maxHeight:number = modalHeight
                - parseInt(styles.paddingTop)
                - parseInt(styles.paddingBottom)
                - parseInt(getComputedStyle(body).marginTop)
                - parseInt(getComputedStyle(body).marginBottom)
                - 10; // Don't know where these 10px come from

            if (title) {
                maxHeight -= Utils.CalculateElementHeight(title);
            }
            if (footer){
                maxHeight -= Utils.CalculateElementHeight(footer);
            }

            body.style.maxHeight = maxHeight + 'px';
        }
    }

    /**
     * Event handler for the close button
     * @param event
     */
    private onClose(event:MouseEvent):void {
        event.preventDefault();
        this.close();
    }

    private onResize():void {
        clearTimeout(this.resizeTimeout);
        this.resizeTimeout = window.setTimeout(this.resize.bind(this), RESIZE_TIMEOUT);
    }

    /**
     * Sets the ARIA attributes for better accessibility
     */
    private setAria():void {
        this.element.setAttribute('role', 'dialog');
        let title:HTMLElement = this.element.querySelector('.modal__title h1') as HTMLElement;

        if (title) {
            if (!title.hasAttribute('id')) {
                // Generate unique id
                title.setAttribute('id', Utils.GenerateUID());
            }

            this.element.setAttribute('aria-labelledby', title.getAttribute('id'));
        }
    }

    /**
     * Sets the focus to the first focusable element. If the modal contains a
     * form, it will be the first field in the form, otherwise, it will be the
     * primary button (if available), or the first button found. If no buttons
     * are present, the close button will receive focus.
     */
    private setFocus():void {
        let target:HTMLElement,
            selectors:Array<string> = [
            // First, check for form elements
            '.modal__body input, .modal__body textarea',
            // Next, check for primary buttons
            '.btn--primary',
            // Next, check for arbitrary buttons
            '.btn',
            // Finally, select the close button
            '.modal__close-button'
        ];

        for (let selector of selectors) {
            target = this.element.querySelector(selector) as HTMLElement;
            if (target) {
                target.focus();
                break;
            }
        }

    }
}
