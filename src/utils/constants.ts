export const defaultWheelContext : IWheelContext = {
    rotation: 0,
    winnerSectorId: 0,
    isRotating: false,
    wheel: {
        UUID: "",
        sectors: []
    }
}

export const delay = (ms: number) => new Promise<String>(resolve => setTimeout(resolve, ms))