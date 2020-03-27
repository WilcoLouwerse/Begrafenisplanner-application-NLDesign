export class TableResponsive {

    constructor(private table:HTMLElement) {
        let columns:NodeList = table.querySelectorAll('thead tr:first-child th');
        let columnHeadings:Array<string> = [];
        let rows:NodeList = table.querySelectorAll('tbody tr');

        table.classList.add('table--responsive');

        for (let i:number = 0; i < columns.length; i++) {
            columnHeadings.push(columns.item(i).textContent);
        }

        for (let i:number = 0; i < rows.length; i++) {
            let rowCols:NodeList = (rows.item(i) as Element).querySelectorAll('td');

            if (rowCols.length === columnHeadings.length) {

                for (let j:number = 0; j < rowCols.length; j++) {
                    (rowCols.item(j) as Element).setAttribute('data-col', columnHeadings[j]);
                }

            }
        }

    }
}
