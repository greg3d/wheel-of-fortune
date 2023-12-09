import {delay} from "../utils/constants";

export const getRandSector = async function(max:number, ms:number) {
    let a = Math.floor(Math.random() * max);
    await delay(300)
    return a;

}