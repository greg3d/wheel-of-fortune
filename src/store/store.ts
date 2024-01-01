import {makeAutoObservable} from "mobx";
import {getRandSector} from "../mocks/random";
import {delay} from "../utils/constants.ts";

class Store {
    sectors: ISector[] = []
    rotation: number = 0
    isRotating: boolean = false
    winnerSectorId: number = 0
    UUID: string = ""
    devMode: boolean

    constructor() {
        this.devMode = process.env.NODE_ENV === "development"
        makeAutoObservable(this)
    }

    async fetchFromServer(body: { action: string, UUID: string } | { action: string }) {
        const url = window.location.href
        const response = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(body)
        })
        return response.json();
    }

    async getWheel() {
        let wheel: IWheel
        if (this.devMode) {
            const response = await fetch('./wheel.json')

            wheel = await response.json()
        } else {
            wheel = await this.fetchFromServer({action: "spinWheel"})
        }
        this.sectors = wheel.sectors
        this.UUID = wheel.UUID
        return true
    }

    async spinWheel(UUID: string) {
        if (!UUID) {
            console.log("spinWheel: UUID not set")
            return
        }
        this.isRotating = true
        try {
            if (this.devMode) {
                this.winnerSectorId = await getRandSector(this.sectors.length, 100)
            } else {
                const out: SectorResponse = await this.fetchFromServer({action: "spinWheel", UUID})
                if (out.UUID === this.UUID) {
                    let cur: number = this.rotation + 2 * 360
                    this.rotation = cur - cur % 360 + (360 / this.sectors.length) * out.sector + 360
                    await delay(3900)
                    this.winnerSectorId = out.sector + 1
                    this.isRotating = false
                } else {
                    this.isRotating = false
                    console.log("spinWheel: wrong UUID")
                }
            }
        } catch (e) {
            console.log(e)
        }
    }
}

const store = new Store()
export default store