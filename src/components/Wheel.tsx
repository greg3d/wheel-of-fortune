import React, {FC, useState} from 'react';
import {useContext} from "react";
import {WheelContext} from "../utils/WheelContext";

const Wheel = () => {
    const {wheel, rotation} = useContext(WheelContext)

// <div className="wheel" style={{transform: `rotate(${rotation}deg)`}}>
    return (
        <div className="wheel-container">
            <div className="wheel">
                {wheel.sectors.map((sector, index) => {
                    let curRot = index * 360 / wheel.sectors.length
                    return (
                        <div key={sector.id} className={"sector"} style={{transform: `translateY(-100%) rotate(-${curRot}deg)`}}>{sector.name}</div>
                    )
                })}
            </div>
        </div>
    );
};

export default Wheel;