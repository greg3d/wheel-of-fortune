import React, { useEffect, useState} from 'react';
import {useContext} from "react";
import {WheelContext} from "../utils/WheelContext";
import {ColorGen} from "../utils/constants";

interface Props {
    propa: number,
    propb: string
}

const Wheel = (props:Props) => {


    const {wheel, rotation, winnerSectorId} = useContext(WheelContext)
    const colors = new ColorGen();
    const [width, setWidth] = useState<number>(0);

    useEffect(()=>{
        const wc = document.querySelector<HTMLDivElement>('.wheel-container') as HTMLDivElement;
        setWidth(wc.clientWidth-20);

        console.log(props.propa)
        console.log(props.propb)
    })

    let w = Math.round(Math.tan(180/ wheel.sectors.length / 57.3) * (width / 2))
    //console.log(Math.round(Math.tan(180/ wheel.sectors.length / 57.3) * (width / 2)))
    return (

        <div className="wheel-container">
            <div className={"wheel-pointer"}>{ winnerSectorId == 0 ? '' : winnerSectorId}</div>
            <div className="wheel" style={{
                width: width + 'px',
                height: width + 'px',
                transform: `rotate(${rotation}deg)`
            }}>

                {wheel.sectors.map((sector, index) => {

                    let curr = index * 360 / wheel.sectors.length;

                    return (
                        <div key={sector.id} className={"sector"}
                             style={{transform: `translateY(-100%) rotate(-${curr}deg)`}}>
                            <div className={"sector-background"} style={{
                                width: 0,
                                height: 0,
                                border: `${w}px solid transparent`,
                                borderLeft: '0 solid transparent',
                                borderRight: `${width/2}px solid #${colors.getNextColor()}`,

                            }}></div>
                            <div className="sector-label"><span className={"sector-number"}>{sector.id}</span><span>{sector.name}</span></div>
                        </div>
                    )
                })}

            </div>
        </div>
    );
};

export default Wheel;