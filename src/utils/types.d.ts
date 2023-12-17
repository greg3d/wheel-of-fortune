interface IWheelContext {
    wheel: IWheel
    rotation: number
    winnerSectorId: number
    isRotating: boolean

    // methods
    spinWheel?: (this:IWheelContext) => void
    setWinnerId?: (sectorId: number) => void
    setWheel?: (wheel:IWheel) => void
    setRotation?: (degrees: number) => void
    setIsRotating?: (val:boolean) => void
}

interface IWheel {
    UUID: string
    sectors: ISector[]
}

interface ISector {
    id: number
    name: string
    image: string
    link: string
}