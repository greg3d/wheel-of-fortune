import './App.css'
import Wheel from "./components/Wheel"
import RotateButton from "./components/RotateButton"
import { useEffect } from "react"
import store from "./store/store.ts"

const App = () => {

    useEffect(() => {
        store.getWheel()
    })

    return (
        <div className="App">
            <Wheel/>
            <RotateButton/>
        </div>
    )
}

export default App;
