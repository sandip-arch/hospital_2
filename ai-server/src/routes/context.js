const express = require("express");

const router = express.Router();

const {
    verifyInternalRequest
} = require("../middleware/internal-auth");

// ========================================
// GET /api/context
// ========================================

router.get("/", verifyInternalRequest, async (req, res) => {
    try {
        /*
         * Laravel will eventually send a trusted,
         * sanitized user context here.
         *
         * For now this is only a development
         * contract.
         */

        const userContext = {
            userId: req.headers["x-ai-user-id"] || null,
            role: req.headers["x-ai-user-role"] || null,
            profileType: req.headers["x-ai-profile-type"] || null,
            profileId: req.headers["x-ai-profile-id"] || null
        };

        return res.status(200).json({
            success: true,
            authenticated: true,
            user: userContext
        });

    } catch (error) {
        console.error("AI Context Error:", error);

        return res.status(500).json({
            success: false,
            error: "Unable to resolve AI user context."
        });
    }
});

module.exports = router;