const eventSource = new EventSource('/live-progress');
const disabledStatuses = ['processing', 'processed'];

// listen to all events (without a specific type)
eventSource.onmessage = (event) => {
    const statuses = JSON.parse(event.data);

    for (const [fileId, status] of Object.entries(statuses)) {
        const el = document.querySelector(`.importFileEl[data-file-id="${fileId}"]`);
        if (!el) continue;

        for (const node of el.childNodes) {

            let shouldDisable = disabledStatuses.includes(status);

            if ('P' === node.tagName ) {
                node.className = `import-${status}`
                node.innerHTML = status;
            } 
            // Check if button element is the first child of its type, to avoid disabling the delete button
            else if ('BUTTON' === node.tagName && node.matches(':first-of-type')) {
                console.log(shouldDisable);
                shouldDisable ? node.setAttribute('disabled', '') : '';
            }
        }
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