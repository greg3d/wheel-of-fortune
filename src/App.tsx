import React, {FC, useContext, useEffect, useState} from 'react';
import './App.css';
import Wheel from "./components/Wheel";
import {WheelContext} from "./utils/WheelContext";
import {defaultWheelContext, delay} from "./utils/constants";
import RotateButton from "./components/RotateButton";
import {getRandSector} from "./mocks/random";

const App = () => {

    const [wheelContext, setWheel] = useState(defaultWheelContext)
    const sectors = wheelContext.wheel.sectors
    const [rotation, setRotation] = useState(defaultWheelContext.rotation)

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

    wheelContext.spinWheel = async () => {
        try {

            //const response = await fetch('http://your-backend-url/api/spin'); // Замените на ваш URL бэкэнда
            //const {sector} = response.data;
            //const sectorIndex = sectors.indexOf(sector);
            let cur: number = rotation

            cur = cur + 2 * 360
            setRotation(cur);

            const sect = await getRandSector(sectors.length, 500);
            setRotation(cur + (360 / sectors.length) * sect);
            await delay(2500)

            console.log(sect);
            console.log(cur);

            //onSpin(sector);
        } catch (error) {
            console.error('Error spinning wheel', error);
        }
    };

    useEffect(() => {

        console.log('useEffectWheelProv')


        ;(async () => {

            try {

                const response = await fetch('./wheel.json')
                await delay(1000)
                const data: IWheel = await response.json()
                let temp: IWheelContext = {...wheelContext}
                temp.wheel = data;
                setWheel(temp)

            } catch (e) {
                console.error(e)
            }


        })();
    }, [])

    return (
        <div className="App">
            <WheelContext.Provider value={wheelContext}>
                <Wheel rotation={rotation}/>
                <RotateButton/>
            </WheelContext.Provider>

        </div>
    );
}

export default App;
