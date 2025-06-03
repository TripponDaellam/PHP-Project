<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<?php include 'Partials/nav.php'; ?>
<aside class="hidden lg:block fixed top-[90px] left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow">
    <?php include 'Partials/left_nav.php'; ?>
</aside>

<main class="ml-0 lg:ml-[200px] mt-24 p-6 flex flex-col h-[calc(100vh-6rem)]">
    <h1 class="text-2xl font-semibold mb-4">Chat with AI</h1>

    <div id="chat-box" class="flex-1 bg-white p-4 rounded shadow overflow-y-auto max-h-[60vh] space-y-4">
        <!-- Messages will be appended here -->
    </div>

    <form id="chat-form" class="mt-4 flex gap-2">
        <input type="text" id="user-input" name="message" placeholder="Ask something..." required
               class="flex-1 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
        <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Send</button>
        <button type="button" id="reset-chat"
                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Reset</button>
    </form>
</main>

<script>
const form = document.getElementById('chat-form');
const input = document.getElementById('user-input');
const chatBox = document.getElementById('chat-box');
const resetBtn = document.getElementById('reset-chat');

function appendMessage(role, message) {
    const msgDiv = document.createElement('div');
    msgDiv.className = role === 'user' ? 'text-right' : '';
    msgDiv.innerHTML = `<span class="inline-block ${role === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'} px-4 py-2 rounded-lg mb-1 whitespace-pre-wrap">${message}</span>`;
    chatBox.appendChild(msgDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const question = input.value.trim();
    if (!question) return;

    appendMessage('user', question);
    input.value = '';
    appendMessage('ai', 'Thinking...');

    const response = await fetch('../Controller/askAi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: question })
    });

    const data = await response.json();
    chatBox.lastChild.remove(); // Remove 'Thinking...'
    appendMessage('ai', data.reply);
});

resetBtn.addEventListener('click', async () => {
    await fetch('ask_ai.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ reset: true }) });
    chatBox.innerHTML = '';
});
</script>

</body>
</html>
