import { useState } from "preact/hooks";

export default function ProgressBar({value = 15, max = 20 }) {

    const percentage = Math.min(100, Math.max(0, (value / max) * 100));

    return (
        <div className="progress-bar">
            <div
                className="progress-bar-fill"
                style={{ width: `${percentage}%` }}
            />
            <span className="progress-stats">15/20</span>
        </div>
    );
}