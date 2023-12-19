import React, {useEffect, useState} from 'react';
import './App.css';
import Wheel from "./components/Wheel";
import {WheelContext} from "./utils/WheelContext";
import {defaultWheelContext, delay} from "./utils/constants";
import RotateButton from "./components/RotateButton";
import {getRandSector} from "./mocks/random";

interface SectorResponse {
    sector: number
    UUID: string
}

const App = () => {

    const [wheelContext, setWheelContext] = useState<IWheelContext>(defaultWheelContext)
    const sectors = wheelContext.wheel.sectors
    const devMode:boolean = process.env.NODE_ENV === "development"

    const setWheel = (newWheel: IWheel): void => {
        setWheelContext((prev) => {
            let temp: IWheelContext = {...prev}
            temp.wheel = newWheel
            return temp;
        })
    }

    const setIsRotating = (val: boolean): void => {
        setWheelContext((prev) => {
            let temp: IWheelContext = {...prev}
            temp.isRotating = val
            return temp;
        })
    }

    const setWinnderId = (val: number): void => {
        setWheelContext((prev) => {
            let temp: IWheelContext = {...prev}
            temp.winnerSectorId = val
            return temp;
        })
    }

    const setRotation = (val: number): void => {
        setWheelContext((prev) => {
            let temp: IWheelContext = {...prev}
            temp.rotation = val
            return temp;
        })
    }

    const spinWheel = async function (): Promise<number> {
        try {
            let sector : number = -1;
            if (devMode) {
                sector = await getRandSector(sectors.length, 100)
            } else {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'spinWheel',
                        UUID: wheelContext.wheel.UUID
                    })
                }); // Замените на ваш URL бэкэнда
                let { sector } = await response.json() as SectorResponse;
            }

            // @ts-ignore
            let cur: number = this.rotation
            cur = cur + 2*360
            // setRotation(cur)
            const val: number = cur - cur % 360 + (360 / sectors.length) * sector + 360
            setRotation(val)
            // console.log(sect, val)
            await delay(3900)
            setWinnderId(sector + 1)
            setIsRotating(false)

            return val;

        } catch (error) {

            console.error('Error spinning wheel: ', error);
            return -1;
        }
    }

    useEffect(() => {
        ;(async () => {
            try {
                if (devMode) {
                    const response = await fetch('./wheel.json')
                    await delay(100)
                    const data: IWheel = await response.json()
                    setWheel(data)
                } else {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: JSON.stringify({
                            action: 'getWheel'
                        })
                    })
                    //await delay(500)
                    const data: IWheel = await response.json()
                    setWheel(data)
                }


            } catch (e) {
                console.error(e)
            }
        })();
    }, [])

    spinWheel.bind(wheelContext)
    wheelContext.spinWheel = spinWheel
    wheelContext.setRotation = setRotation
    wheelContext.setIsRotating = setIsRotating
    wheelContext.setWheel = setWheel
    wheelContext.setWinnerId = setWinnderId


    let aaa = 455;
    let bbb = "dfdsafsadf";

    return (
        <div className="App">
            <WheelContext.Provider value={wheelContext}>
                <Wheel propa={aaa} propb={bbb}/>
                <RotateButton/>
            </WheelContext.Provider>
        </div>
    );

}

/*
const setRotation = (val?:number) : void => {

    let temp: IWheelContext = {...wheelContext}
    let cur = temp.rotation

    if (val === undefined) {

        temp.rotation = (cur + 360*2)
    } else {
        temp.rotation = (cur + 360*2) + val
    }


    setWheel(temp)
    console.log(temp.rotation);
}*/


export default App;
