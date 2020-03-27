const INNER_COLOR: string = '#ffffff';
const GLOBAL_ALPHA_OPACITY: number = 0.2;

/**
 * @class DonutChart
 * Creates a donut chart.
 */
export class DonutChart{

    private _value: number = 0;
    private color: string;
    private canvas: HTMLCanvasElement;
    private ctx: CanvasRenderingContext2D;
    private xCoord: number;
    private yCoord: number;
    private radius: number;
    private arcWidth: number;

    constructor(private element:HTMLElement) {
        if (element) {
            this.setup();
            this.setupListeners();
        } else {
            throw new Error('No element provided');
        }
    }

    set value(value: number) {
        if (value <= 100 && value >= 0) {
            this._value = value;
        } else {
            throw new Error('Value should be a percentage and therefore between (and including) 0 and 100');
        }
    }

    get value(): number {
        return this._value;
    }

    private setup(): void {
        this.canvas = this.element.getElementsByTagName('canvas')[0];
        this.ctx = this.canvas.getContext('2d');

        this.setColor();
        this.setSize();
        this.setInner();
    }

    private setInner(): void {
        const innerDiv: HTMLElement = this.element.getElementsByClassName('donut-chart__inner')[0] as HTMLElement;

        if (innerDiv) {
            const innerRadius: number = (2 * this.radius) - this.arcWidth;
            // inner div should be the biggest square that could fit inside the inner radius of the chart.
            const innerSize: number = Math.sqrt(Math.pow(innerRadius, 2) / 2);
            innerDiv.style.width = `${innerSize}px`;
            innerDiv.style.height = `${innerSize}px`;
        } else {
            throw new Error('No element with class "donut-chart__inner" found');
        }
    }

    private setupListeners(): void {
        window.addEventListener('resize', this.debounce(() => this.updateSize(), 250));
    }

    /**
     * draws the donut chart.
     */
    public draw(): void {
        const offset: number = this.calcOffset() ;
        const switchPoint: number =  this.calcSwitchPoint(offset);
        const startPoint: number = this.calcStartPoint(offset);
        const endPoint: number = this.calcEndPoint(offset);

        // fill chart until given value.
        this.ctx.beginPath();
        this.ctx.lineWidth = this.arcWidth;
        this.ctx.arc(this.xCoord, this.yCoord, this.radius, startPoint, switchPoint);
        this.ctx.strokeStyle = this.color;
        this.ctx.stroke();

        // fill remaining part of chart.
        this.ctx.beginPath();
        this.ctx.arc(this.xCoord, this.yCoord, this.radius, switchPoint, endPoint);
        this.ctx.strokeStyle = this.color;
        this.ctx.globalAlpha = GLOBAL_ALPHA_OPACITY;
        this.ctx.stroke();

        // fill center. Otherwise it is a pie, not a donut.
        this.ctx.beginPath();
        this.ctx.fillStyle = INNER_COLOR;
        this.ctx.fill();
    }

    private calcOffset(): number {
        return -Math.PI * 0.5;
    }

    private calcStartPoint(offset: number): number {
        return 0 + offset;
    }

    private calcSwitchPoint(offset: number): number {
        return ((this._value / 100 ) * (Math.PI * 2)) + offset;
    }

    private calcEndPoint(offset: number): number {
        return (Math.PI * 2) + offset;
    }

    private setColor(): void {
        const styles: CSSStyleDeclaration = window.getComputedStyle(this.element);

        this.color = styles.color;
    }

    private setSize(): void {
        const size: number = this.canvas.clientWidth;
        const dpi: number = window.devicePixelRatio;
        this.canvas.style.height = `${size}px`;
        this.canvas.height = size * dpi;
        this.canvas.width = size * dpi;
        this.yCoord = (size * dpi) / 2;
        this.xCoord = (size * dpi) / 2;
        this.arcWidth = 0.1 * (size * dpi);
        this.radius = ((size * dpi) / 2) - this.arcWidth;
    }

    private updateSize(): void {
        this.setSize();
        this.setInner();
        this.draw();
    }

    public destroy(): void {
        window.removeEventListener('resize', this.debounce(() => this.updateSize(), 250));
    }

    private debounce(func: Function, timeout: number): EventListenerOrEventListenerObject {
        let timer: number;

        return (...args: any[]) => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => { func(...args); }, timeout);
        };
    }
}
