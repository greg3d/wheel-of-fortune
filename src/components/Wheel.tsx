import React, {FC, useEffect, useState} from 'react';
import {useContext} from "react";
import {WheelContext} from "../utils/WheelContext";
import {ColorGen} from "../utils/constants";
import {cursorTo} from "readline";
// import {animated, useSpring} from "@react-spring/web";

const Wheel = () => {
    const {wheel, rotation, winnerSectorId} = useContext(WheelContext)
    const colors = new ColorGen();

    useEffect(
        ()=>{

        }
    )

    return (
        <div className="wheel-container">
            <div className={"pointer"}>{ winnerSectorId == 0 ? '' : winnerSectorId}</div>
            <div className="wheel" style={{transform: `rotate(${rotation}deg)`}}>
                {wheel.sectors.map((sector, index) => {
                    let curRot = index * 360 / wheel.sectors.length
                    let shift = 360 / wheel.sectors.length / 2;
                    //let curRot2 = curRot + shift;
                    let www = 480 / wheel.sectors.length;
                    return (
                        <div key={sector.id} className={"sector"}
                             style={{transform: `translateY(-100%) rotate(-${curRot}deg)`}}>
                            <svg>

                                <path d="M115,115 L115,5 A110,110 1 0,1 190,35 z"></path>
                            </svg>

                            {sector.id+1}: {sector.name}
                        </div>
                    )
                })}
            </div>
        </div>
    );
};

export default Wheel;

/*<div className={"background"} style={{
                       border: `${www}px solid transparent`,
                       borderRight: '165px solid black',
                       borderRightColor: colors.getColor(),
                   }}/>*/