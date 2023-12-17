export const defaultWheelContext : IWheelContext = {
    rotation: 0,
    winnerSectorId: 0,
    isRotating: false,
    wheel: {
        UUID: "",
        sectors: []
    }
}

export const delay = (ms: number) => new Promise<String>(resolve => setTimeout(resolve, ms))

export class ColorGen {

    colors: string[]
    count: number
    max: number

    constructor() {
        this.colors = [
            '#bb33ee',
            '#ffcc44',
            '#ff2244',
            '#00bb88',
            '#a781b3',
            '#f84c8f',
            '#37d916',
        ]
        this.count = 0;
        this.max = this.colors.length
    }

    getColor():string {
        let cur = this.count;
        this.count++;

        if (this.count >= this.max) {
            this.count = 0;
        }

        return this.colors[cur];
    }
}