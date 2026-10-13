const GROQ_API_URL = "https://api.groq.com/openai/v1/chat/completions";

async function generateAIResponse(messages) {
    const apiKey = process.env.GROQ_API_KEY;

    if (!apiKey) {
        throw new Error("GROQ_API_KEY is not configured.");
    }

    const response = await fetch(GROQ_API_URL, {
        method: "POST",
        headers: {
            "Authorization": `Bearer ${apiKey}`,
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            model: process.env.GROQ_MODEL || "openai/gpt-oss-20b",
            messages,
            temperature: 0.3,
            max_completion_tokens: 1000
        })
    });

    if (!response.ok) {
        const errorText = await response.text();

        throw new Error(
            `Groq API error (${response.status}): ${errorText}`
        );
    }

    const data = await response.json();

    return data.choices?.[0]?.message?.content || "I couldn't generate a response.";
}

module.exports = {
    generateAIResponse
};