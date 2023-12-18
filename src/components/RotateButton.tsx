import React, {useContext, useState} from 'react';
import {WheelContext} from "../utils/WheelContext";

const RotateButton = () => {

    const wc = useContext(WheelContext)
    const [isRot, setIsRot] = useState<boolean>(false)

    const clickHandle = function (e: React.MouseEvent<HTMLButtonElement>) {
        setIsRot(true);
        e.preventDefault()
        if (wc.setIsRotating && wc.spinWheel) {
            wc.setIsRotating(true)
            wc.spinWheel()
        }
    }
    return (

        <button onClick={clickHandle} className={"button is-primary is-large"} disabled={isRot}>
            Крутить!
        </button>

    );
};

export default RotateButton;