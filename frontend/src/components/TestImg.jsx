import { useState } from "preact/hooks";

export default function TestImg(props) {

    const [src, setSrc] = useState("images/blank.gif");

    return (
        <img id="main-img" src={src} alt="Obrázek přírodniny" />
    );
}