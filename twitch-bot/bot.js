require('dotenv').config();
const tmi = require('tmi.js');
const axios = require('axios');

const client = new tmi.Client({
    identity: {
        username: process.env.TWITCH_USERNAME,
        password: process.env.TWITCH_OAUTH
    },
    channels: [process.env.TWITCH_CHANNEL]
});

client.connect().catch(console.error); // Always catch errors

client.on('connected', (addr, port) => {
    console.log(`✅ Connected to ${addr}:${port}`);
});

client.on('join', (channel, username, self) => {
    if (self) {
        console.log(`✅ Joined channel ${channel} as ${username}`);
    }
});

client.on('message', (channel, tags, message, self) => {
    console.log(`[${channel}] ${tags.username}: ${message}`);
});


client.on('message', async (channel, tags, message, self) => {
    if (self || !message.startsWith('!songrequest')) return;

    const query = message.replace('!songrequest', '').trim();

    if (!query) {
        client.say(channel, `@${tags.username}, please specify a song and artist.`);
        return;
    }

    try {
        const res = await axios.post(process.env.API_URL, { query });
        client.say(channel, `🎶 Added: ${res.data.title} by ${res.data.artist}`);
    } catch (error) {
        console.error('Error adding song:', error.response?.data || error.message);
        client.say(channel, `❌ Sorry, @${tags.username}, couldn't add that song.`);
    }
});
