export default function AnswerCard({ name = "Název přírodniny", src, userAns, isCorrect }) {
    return (
        <div className={`answer-card ${isCorrect ? "correct" : "incorrect"}`}>
            <div className="picture">
                <img src={src} alt={name} />
                <img className="badge" src={isCorrect ? "images/tick-thick.svg" : "images/cross-thick.svg"}
                    alt={isCorrect ? "Správně" : "Špatně"} />
            </div>
            <div className="name">
                {!isCorrect && <div className="user-answer">{userAns || "bez odpovědi"}</div>}
                {name}
            </div>
        </div>
    );
}