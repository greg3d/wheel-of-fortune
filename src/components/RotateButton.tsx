import React, {useState} from 'react';
import store from "../store/store.ts";
import {observer} from "mobx-react";
const RotateButton = () => {

    const [isRot, setIsRot] = useState<boolean>(false)

    const clickHandle = function (e: React.MouseEvent<HTMLButtonElement>) {
        setIsRot(true);
        e.preventDefault()
        store.isRotating = true;
        store.spinWheel(store.UUID)
    }
    return (

        <button onClick={clickHandle} className={"button is-primary is-large"} disabled={isRot}>
            Крутить!
        </button>

    );
};

export default observer(RotateButton);