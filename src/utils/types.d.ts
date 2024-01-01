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

interface SectorResponse {
    sector: number
    UUID: string
}