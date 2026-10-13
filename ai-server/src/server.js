require("dotenv").config();

const express = require("express");
const cors = require("cors");

const chatRouter = require("./routes/chat");
const contextRouter = require("./routes/context");

const app = express();

const PORT = process.env.PORT || 3001;

// ========================================
// Middleware
// ========================================

app.use(cors());
app.use(express.json());

// ========================================
// Root
// ========================================

app.get("/", (req, res) => {
    res.json({
        success: true,
        service: "Hospital AI Server",
        message: "AI backend is running"
    });
});

// ========================================
// Health Check
// ========================================

app.get("/api/health", (req, res) => {
    res.json({
        success: true,
        service: "Hospital AI Server",
        status: "online"
    });
});

// ========================================
// AI Chat
// ========================================

app.use("/api/chat", chatRouter);

// ========================================
// AI Identity Context
// ========================================

app.use("/api/context", contextRouter);

// ========================================
// Start Server
// ========================================

app.listen(PORT, () => {
    console.log("========================================");
    console.log("   Hospital AI Server");
    console.log("========================================");
    console.log(`Server: http://localhost:${PORT}`);
    console.log(`Health: http://localhost:${PORT}/api/health`);
    console.log(`Chat:   http://localhost:${PORT}/api/chat`);
    console.log(`Context:http://localhost:${PORT}/api/context`);
    console.log("========================================");
});