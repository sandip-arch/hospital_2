const crypto = require("crypto");

function timingSafeEqualString(a, b) {
    if (!a || !b) {
        return false;
    }

    const aBuffer = Buffer.from(a, "utf8");
    const bBuffer = Buffer.from(b, "utf8");

    if (aBuffer.length !== bBuffer.length) {
        return false;
    }

    return crypto.timingSafeEqual(aBuffer, bBuffer);
}

function verifyInternalRequest(req, res, next) {
    const receivedSecret = req.headers["x-ai-internal-secret"];
    const expectedSecret = process.env.AI_INTERNAL_SECRET;

    if (!expectedSecret) {
        console.error("AI_INTERNAL_SECRET is not configured.");

        return res.status(500).json({
            success: false,
            error: "AI internal authentication is not configured."
        });
    }

    if (!timingSafeEqualString(receivedSecret, expectedSecret)) {
        return res.status(401).json({
            success: false,
            error: "Unauthorized AI request."
        });
    }

    next();
}

module.exports = {
    verifyInternalRequest
};