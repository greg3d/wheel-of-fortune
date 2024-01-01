export const delay = (ms: number) => new Promise<String>(resolve => setTimeout(resolve, ms))

export class ColorGen {

    colors: string[]
    count: number
    max: number

    constructor() {
        this.colors = ["f94144","f3722c","f8961e","f9844a","f9c74f","90be6d","43aa8b","4d908e","577590","277da1"];
        this.count = 0;
        this.max = this.colors.length
    }

    getRandColor():string {
        let cur = Math.floor(Math.random() * this.max);
        return this.colors[cur];
    }

    getNextColor():string {
        let cur = this.count;
        this.count++;

        if (this.count >= this.max) {
            this.count = 0;
        }

        return this.colors[cur];
    }
}