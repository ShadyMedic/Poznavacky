export default function AnswerCard({ name = "Název přírodniny", src }) {
    return (
        <div className="answer-card">
            <div className="picture">    
                <img src={src} />
            </div>
            <div className="name">{name}</div>
        </div>
    );
}