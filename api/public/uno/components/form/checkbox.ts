import '../../core/polyfills';

/**
 * @class CheckboxGroup
 * Adds support for grouped checkboxes in tables or lists
 * Usage:
 * System.import('/node_modules/uno/dist/components/form/checkbox.js').then(function (module) {
 *    var checkboxGroup = document.getElementById('table_nested');
 *    new module.CheckboxGroup(checkboxGroup);
 * });
 */
export class CheckboxGroup implements EventListenerObject {

    constructor(private container: Element) {
        if (this.container.nodeName.toLowerCase() === 'ul' || this.container.nodeName.toLowerCase() === 'table') {
            let checkboxes: any = this.container.querySelectorAll('.input__control--checkbox');

            checkboxes.forEach((checkbox: HTMLInputElement) => {
                checkbox.addEventListener('click', this);
            });
        } else {
            throw new Error(`Incompatible element passed, expected <ul> or <table>, got ${this.container.nodeName}`);
        }
    }

    public destroy(): void {
        let checkboxes: any = this.container.querySelectorAll('.input__control--checkbox');
        checkboxes.forEach((checkbox: HTMLInputElement) => {
            checkbox.removeEventListener('click', this);
        });
    }

    handleEvent(evt: Event): void {
        if (evt.type === 'click') {
            const parent: HTMLElement = (evt.currentTarget as HTMLElement).parentElement;
            if (parent.nodeName.toLowerCase() === 'li') {
                // List mode
                this.onListCheckboxClick(evt.currentTarget as HTMLInputElement);
            } else {
                // Table mode
                this.onTableCheckboxClick(evt.currentTarget as HTMLInputElement);
            }
        }
    }

    private onListCheckboxClick(checkbox: HTMLInputElement): void {
        let parent: HTMLElement = checkbox.parentElement;
        if (parent.classList.contains('input__group--checkbox')) {
            // group checkbox
            const ul: Element = parent.querySelector('ul');

            if (ul) {
                const checkboxes: any = ul.querySelectorAll('.input__control--checkbox');
                checkboxes.forEach((child: HTMLInputElement) => child.checked = checkbox.checked);
            }
        } else {
            // child checkbox
            while (parent && !parent.classList.contains('input__group')) {
                parent = parent.parentNode as HTMLElement;
            }

            if (parent) {
                const ul: Element = parent.querySelector('ul');
                const parentCheckbox: HTMLInputElement = parent.querySelector('.input__control--checkbox') as HTMLInputElement;
                const checkboxesLength: number = ul.querySelectorAll('.input__control--checkbox').length;
                const checkedCount: number = ul.querySelectorAll('.input__control--checkbox:checked').length;

                parentCheckbox.checked = checkedCount > 0;
                parentCheckbox.indeterminate = checkedCount > 0 && checkedCount < checkboxesLength;
            }
        }
    }

    private onTableCheckboxClick(checkbox: HTMLInputElement): void {
        let table: HTMLElement = checkbox.parentElement;
        while (table && table.nodeName.toLowerCase() !== 'table') {
            table = table.parentNode as HTMLElement;
        }
        const checkboxes: any = table.querySelectorAll('.input__control--checkbox');
        if (table.querySelector('.input__control--checkbox') === checkbox) {
            // Group checkbox item
            checkboxes.forEach((child: HTMLInputElement, idx: number) => {
                // Skip first item, that's the group checkbox
                if (idx > 0) {
                    child.checked = checkbox.checked;
                }
            });
        } else {
            // Child checkbox
            const checkboxesLength: number = checkboxes.length - 1;
            let checkedCount: number = table.querySelectorAll('tbody .input__control--checkbox:checked').length;

            checkboxes.item(0).checked = checkedCount > 0;
            checkboxes.item(0).indeterminate = checkedCount > 0 && checkedCount < checkboxesLength;
        }
    }

}
