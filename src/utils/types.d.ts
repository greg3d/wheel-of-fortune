interface IWheelContext {
    wheel: IWheel
    rotation: number
    winnerSectorId: number
    isRotating: boolean

    // methods
    spinWheel?: (this:IWheelContext) => Promise<number>
    setWinnerId?: (sectorId: number) => void
    setWheel?: (wheel:IWheel) => void
    setRotation?: (degrees: number) => void
    setIsRotating?: (val:boolean) => void
}

interface IWheel {
    UUID: String
    sectors: ISector[]
}

interface ISector {
    id: number
    name: String
    image: String
    link: String
}
