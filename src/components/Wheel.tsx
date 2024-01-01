import {useEffect, useState} from 'react';
import {ColorGen} from "../utils/constants";
import store from "../store/store.ts";
import { observer } from "mobx-react"

const Wheel = () => {

    const colors = new ColorGen();
    const [width, setWidth] = useState<number>(0);

    useEffect(() => {
        const wc = document.querySelector<HTMLDivElement>('.wheel-container') as HTMLDivElement;
        setWidth(wc.clientWidth - 20);
    })

    let w = Math.round(Math.tan(180 / store.sectors.length / 57.3) * (width / 2))

    return (
        <div className="wheel-container">
            {store.winnerSectorId != 0 ? <div className={"wheel-pointer"}>{store.winnerSectorId}</div> : ''}
            <div className="wheel" style={{
                width: width + 'px',
                height: width + 'px',
                transform: `rotate(${store.rotation}deg)`
            }}>
                {store.sectors.map((sector: ISector, index: number) => {
                    let curr = index * 360 / store.sectors.length;
                    return (
                        <div key={sector.id} className={"sector"}
                             style={{transform: `translateY(-100%) rotate(-${curr}deg)`}}>
                            <div className={"sector-background"} style={{
                                width: 0,
                                height: 0,
                                border: `${w}px solid transparent`,
                                borderLeft: '0 solid transparent',
                                borderRight: `${width / 2}px solid #${colors.getNextColor()}`,

                            }}></div>
                            <div className="sector-label"><span
                                className={"sector-number"}>{sector.id + 1}</span><span>{sector.name}</span></div>
                        </div>
                    )
                })}
            </div>
        </div>
    );
};
export default observer(Wheel);