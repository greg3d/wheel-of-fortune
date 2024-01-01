import {makeAutoObservable, flow} from "mobx";
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
        makeAutoObservable(this, {
            spinWheel: flow,
            getWheel: flow
        })
    }

    async fetchFromServer(body: { action: string, UUID: string } | { action: string }) {
        const url = window.location.href
        const response = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(body)
        })
        return response.json();
    }

    async fetchFromLocal(url: string) {
        const res = await fetch(url)
        return await res.json()
    }

    * getWheel() {
        let wheel: IWheel
        try {
            if (this.devMode) {
                wheel = yield this.fetchFromLocal('./wheel.json')
            } else {
                wheel = yield this.fetchFromServer({action: "spinWheel"})
            }
            this.sectors = wheel.sectors
            this.UUID = wheel.UUID
            return wheel
        } catch (e) {
            console.log(e)
        }
    }

    * spinWheel(UUID: string) {
        if (!UUID) {
            console.log("spinWheel: UUID not set")
            return
        }
        this.isRotating = true
        let tempWinner = 0
        try {
            if (this.devMode) {
                tempWinner = yield getRandSector(this.sectors.length, 100)
            } else {
                const out: SectorResponse = yield this.fetchFromServer({action: "spinWheel", UUID})
                if (!this.devMode && out.UUID === this.UUID) {
                    tempWinner = out.sector
                } else {
                    this.isRotating = false
                    console.log("spinWheel: wrong UUID")
                }
            }
            let cur: number = this.rotation + 2 * 360
            this.rotation = cur - cur % 360 + (360 / this.sectors.length) * tempWinner + 360
            yield delay(3900)
            this.winnerSectorId = tempWinner + 1
            this.isRotating = false
            console.log(this.winnerSectorId)
        } catch (e) {
            console.log(e)
        }
    }
}

const store = new Store()
// const wheel = await flowResult(store.getWheel())
export default store