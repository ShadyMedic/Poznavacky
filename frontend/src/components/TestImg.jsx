export default function TestImg( {picture} ) {

    const src = (picture == null) ? "images/blank.gif" : picture.url ;

    return (
        <img id="main-img" src={src} alt="Obrázek přírodniny" />
    );
}