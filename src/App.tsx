import React, {useEffect, useState} from 'react';
import './App.css';
import Wheel from "./components/Wheel";
import {WheelContext} from "./utils/WheelContext";
import {defaultWheelContext, delay} from "./utils/constants";
import RotateButton from "./components/RotateButton";
import {getRandSector} from "./mocks/random";

const App = () => {

    const [wheelContext, setWheelContext] = useState<IWheelContext>(defaultWheelContext)
    const sectors = wheelContext.wheel.sectors

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

    const setRotation = (val: number): void => {
        setWheelContext((prev) => {
            let temp: IWheelContext = {...prev}
            temp.rotation = val
            return temp;
        })
    }

    const spinWheel = async (): Promise<number> => {
        try {

            //const response = await fetch('http://your-backend-url/api/spin'); // Замените на ваш URL бэкэнда
            //const {sector} = response.data;
            //const sectorIndex = sectors.indexOf(sector);

            // @ts-ignore
            let cur: number = this != undefined ? this.rotation : 0

            cur = cur + 2 * 360
            setRotation(cur);

            const sect = await getRandSector(sectors.length, 500);
            const val: number = cur + (360 / sectors.length) * sect;
            setRotation(val);
            // await delay(2500)
            return val;

        } catch (error) {

            console.error('Error spinning wheel: ', error);
            return -1;
        }
    }


    useEffect(() => {
        ;(async () => {
            try {
                const response = await fetch('./wheel.json')
                await delay(500)
                const data: IWheel = await response.json()
                setWheel(data)
            } catch (e) {
                console.error(e)
            }
        })();
    }, [])

    setRotation.bind(wheelContext)
    wheelContext.spinWheel = spinWheel
    wheelContext.setRotation = setRotation
    wheelContext.setIsRotating = setIsRotating
    wheelContext.setWheel = setWheel

    return (
        <div className="App">
            <WheelContext.Provider value={wheelContext}>
                <Wheel/>
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
