require('dotenv').config({ path: __dirname + '/.env' });
const tmi = require('tmi.js');
const axios = require('axios');

// Fetch a fresh Twitch token from Laravel
const getAccessToken = async () => {
    try {
        console.log("🔍 Token API URL:", process.env.TOKEN_API_URL);
        const res = await axios.get(process.env.TOKEN_API_URL);
        return `oauth:${res.data.access_token}`;
    } catch (err) {
        console.error("❌ Failed to get access token from Laravel:", err.response?.data || err.message);
        process.exit(1);
    }
};

(async () => {
    const password = await getAccessToken();

    const client = new tmi.Client({
        identity: {
            username: process.env.TWITCH_USERNAME,
            password,
        },
        channels: [process.env.TWITCH_CHANNEL],
    });

    client.connect().catch(console.error);

    client.on('connected', (addr, port) => {
        console.log(`✅ Connected to ${addr}:${port}`);
    });

    client.on('join', (channel, username, self) => {
        if (self) {
            console.log(`✅ Joined channel ${channel} as ${username}`);
        }
    });

    client.on('disconnected', (reason) => {
        console.log('❌ Bot disconnected:', reason);
    });

    client.on('reconnect', () => {
        console.log('🔁 Attempting to reconnect to Twitch...');
    });

    client.on('message', async (channel, tags, message, self) => {
        if (self) return;

        console.log(`[${channel}] ${tags.username}: ${message}`);

        if (!message.startsWith('!songrequest')) return;

        const query = message.replace('!songrequest', '').trim();

        if (!query) {
            client.say(channel, `@${tags.username}, please specify a song and artist.`);
            return;
        }

        try {
            const res = await axios.post(process.env.API_URL, { query });
            client.say(channel, `🎶 Added: ${res.data?.title ?? res.data?.song?.title ?? 'Unknown'} by ${res.data?.artist ?? res.data?.song?.artist ?? 'Unknown'}`);
        } catch (error) {
            console.error('Error adding song:', error.response?.data || error.message);
            client.say(channel, `❌ Sorry, @${tags.username}, couldn't add that song.`);
        }
    });
})();
