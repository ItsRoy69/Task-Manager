const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

// Connect to MongoDB
mongoose.connect(process.env.MONGODB_URI || 'mongodb://localhost:27017/task_logger', {
    useNewUrlParser: true,
    useUnifiedTopology: true
})
.then(() => console.log('MongoDB connected'))
.catch(err => console.error('MongoDB connection error:', err));

// Middleware
app.use(cors());
app.use(express.json());

// Log Model
const logSchema = new mongoose.Schema({
    task_id: { type: Number, required: true },
    user_id: { type: Number, required: true },
    action: { type: String, required: true },
    task_data: { type: Object, required: true },
    timestamp: { type: Date, default: Date.now }
});

const Log = mongoose.model('Log', logSchema);

// Routes
app.post('/api/logs', async (req, res) => {
    try {
        const log = new Log(req.body);
        await log.save();
        res.status(201).json({ message: 'Log created successfully', log });
    } catch (error) {
        res.status(400).json({ message: 'Error creating log', error: error.message });
    }
});

app.get('/api/logs', async (req, res) => {
    try {
        const logs = await Log.find().sort({ timestamp: -1 });
        res.status(200).json({ logs });
    } catch (error) {
        res.status(400).json({ message: 'Error fetching logs', error: error.message });
    }
});

// Start server
app.listen(PORT, () => {
    console.log(`Logger service running on port ${PORT}`);
});