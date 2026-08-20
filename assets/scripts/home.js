const eventSource = new EventSource('/live-progress');

// listen to all events (without a specific type)
eventSource.onmessage = (event) => {
    const statuses = JSON.parse(event.data);
    console.log('test');

    for (const [fileId, status] of Object.entries(statuses)) {
        const el = document.querySelector(`.statusEl[data-file-id="${fileId}"]`);
        if (!el) continue;

        el.innerHTML = 'uploaded' === status 
            ? `<button type="submit"> Import </button>`
            : `<p class="import-${status} importStatus">${status}</p>`;
    }
};

// listen to events with a specific type
// eventSource.addEventListener('my-event', (event) => {
//     console.log('My event:', JSON.parse(event.data));
// });

// handle connection errors
eventSource.onerror = (error) => {
    console.error('SSE error:', error, error.data);
};