import React, {useContext} from 'react';
import {WheelContext} from "../utils/WheelContext";

const RotateButton = () => {

    const {spinWheel, isRotating} = useContext(WheelContext)

    const clickHandle = function(e: React.MouseEvent<HTMLButtonElement>){
        e.preventDefault()
        //e.currentTarget.disabled = true
        spinWheel();
    }
    return (
        <div>
            <button onClick={clickHandle} className={"button is-primary"} disabled={isRotating} >
                Крутить!
            </button>
        </div>
    );
};

export default RotateButton;