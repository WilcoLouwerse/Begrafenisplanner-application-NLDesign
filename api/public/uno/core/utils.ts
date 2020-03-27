export class Utils {

    /**
     * Generates an unique identifier
     * @return {string}
     * @constructor
     */
    static GenerateUID(): string {
        function s4(): string {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }

        return `uno-${s4()}${s4()}-${s4()}-${s4()}-${s4()}-${s4()}${s4()}${s4()}`;
    }

    /**
     * Traverses the dom tree up to find the element containing className
     * @param child
     * @param className The className to find
     * @param root If root is reached while traversing, searching stops and null is returned
     * @return {any}
     */
    static FindParentContainingClass(child: Element, className: string, root: Element): Element {
        while (child !== root) {
            if (child.classList.contains(className)) {
                return child;
            }
            child = child.parentElement;
        }

        return null;
    }

    static IsDescendant(child: Element, parent: Element): boolean {
        let node: Node = child.parentNode;
        while (node !== null) {
            if (node === parent) {
                return true;
            }
            node = node.parentNode;
        }
        return false;
    }

    /**
     * Calculates the total height of an element, combining height & margin
     * @param element
     * @return {number}
     */
    static CalculateElementHeight(element: Element): number {
        let height: number = 0;

        if (element) {
            let styles: CSSStyleDeclaration = getComputedStyle(element);
            height += parseInt(styles.height);
            height += parseInt(styles.marginTop);
            height += parseInt(styles.marginBottom);
        }

        return height;
    }

    static CreateNode(html: string): HTMLElement {
        return new DOMParser().parseFromString(html, 'text/html').body.firstChild as HTMLElement;
    }

    /**
     * Turns a string-like-this into a StringLikeThis
     * @param {string} string
     * @return {string}
     * @constructor
     */
    static CamelCase(dashed: string): string {
        return dashed.split('-').map((item: string) => item.substring(0, 1).toUpperCase() + item.substring(1).toLowerCase()).join('');
    }

    /**
     * Checks if a certain key is pressed, converts keynames to browser specific names.
     * For instance, the 'ArrowDown' key in Chrome is called 'ArrowDown', in IE it's called 'Down'
     * @param event The original event
     * @param key The name of the key, as specified in the docs (https://developer.mozilla.org/nl/docs/Web/API/KeyboardEvent/key/Key_Values)
     * @constructor
     */
    static IsKeyPressed(event: KeyboardEvent, key: string): boolean {
        const ambiguous: any = {
            ' ': ['Space', 'Spacebar', 'Space Bar'],
            'ArrowDown': ['Down'],
            'ArrowLeft': ['Left'],
            'ArrowRight': ['Right'],
            'ArrowUp': ['Up'],
            'ContextMenu': ['Apps'],
            'CrSel': ['Crsel'],
            'Delete': ['Del'],
            'Escape': ['Esc'],
            'ExSel': ['Exsel']
        };

        if (event.key === key) {
            return true;
        }

        if (ambiguous.hasOwnProperty(key)) {
            return ambiguous[key].reduce(
                (pressed: boolean, alt: string) => {
                    pressed = pressed || event.key === alt;
                    return pressed;
                },
                false);
        }

        return false;
    }

    /**
     * Focuses the next item in the nodeList (or previous with shift-modifier)
     * @param event the keyboard event
     * @param nodeList the collection of focusable items
     */
    static FocusChild(event: KeyboardEvent, nodeList: NodeList): void {
        // get list of focusable items
        let focusableItems: Array<HTMLElement>;
        // convert Nodelist to Array using array prototype
        focusableItems = Array.prototype.slice.call(nodeList, 0);

        // sort according to tabindex
        focusableItems.sort(function (a: HTMLElement, b: HTMLElement): number {
            let aInd: String = a.getAttribute('tabindex');
            let bInd: String = b.getAttribute('tabindex');
            if (aInd > bInd) {
                return 1;
            } else if (aInd < bInd) {
                return -1;
            }
            return 0;
        });

        // get currently focused item
        let focusedItem: HTMLElement;
        focusedItem = document.querySelector(':focus') as HTMLElement;

        // get the number of focusable items
        let numberOfFocusableItems: number;
        numberOfFocusableItems = focusableItems.length;

        // get the index of the currently focused item
        let focusedItemIndex: number;
        focusedItemIndex = Array.prototype.indexOf.call(focusableItems, focusedItem);

        if (event.shiftKey) {
            // back tab
            // if focused on first item and user preses back-tab, go to the last focusable item
            if (focusedItemIndex === 0) {
                focusableItems[numberOfFocusableItems - 1].focus();
                event.preventDefault();
            } else {
                focusableItems[focusedItemIndex - 1].focus();
                event.preventDefault();
            }

        } else {
            // forward tab
            // if focused on the last item and user preses tab, go to the first focusable item
            if (focusedItemIndex === numberOfFocusableItems - 1) {
                focusableItems[0].focus();
                event.preventDefault();
            } else {
                focusableItems[focusedItemIndex + 1].focus();
                event.preventDefault();
            }
        }
    }
}
