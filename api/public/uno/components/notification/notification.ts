/**
 * @class Notification
 * Listens for click events on the close button for notification to close the notification using javascript
 */
export class Notification{

    constructor(private element:HTMLElement) {
    }

    /**
     * Closes the notification
     */
    public close():void {
        this.element.parentNode.removeChild(this.element);
    }
}
