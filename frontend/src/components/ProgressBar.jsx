import { useState } from "preact/hooks";

export default function ProgressBar({current, max}) {

    const percentage = Math.min(100, Math.max(0, (current / max) * 100));

    return (
        <div className="progress-bar">
            <div
                className="progress-bar-fill"
                style={{ width: `${percentage}%` }}
            />
            <span className="progress-stats">{current}/{max}</span>
        </div>
    );
}