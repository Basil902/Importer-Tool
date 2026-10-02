const eventSource = new EventSource('/live-progress');
const disabledStatuses = ['processing', 'processed', 'error'];

// listen to all events (without a specific type)
eventSource.onmessage = (event) => {
    const statuses = JSON.parse(event.data);

    for (const [fileId, status] of Object.entries(statuses)) {
        const el = document.querySelector(`.importFileEl[data-file-id="${fileId}"]`);

        if (!el) continue;

        for (const node of el.childNodes) {

            let shouldDisable = disabledStatuses.includes(status);
            const viewLogsBtn = el.querySelector('.viewLogsBtn');

            if ('P' === node.tagName ) {
                node.className = `import-${status}`
                node.innerHTML = status;
            } 
            // Check if import button element is the first child of its type, to avoid disabling the delete button
            else if ('BUTTON' === node.tagName && node.matches(':first-of-type')) {
                shouldDisable ? node.setAttribute('disabled', '') : '';

                if (node.hasAttribute('disabled')) {
                    node.style.cursor = 'not-allowed';
                }
            }

            if ('error' === status) {
                    viewLogsBtn.removeAttribute('disabled');
                    viewLogsBtn.removeAttribute('hidden');
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