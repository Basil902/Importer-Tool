const eventSource = new EventSource('/live-progress');
const disabledStatuses = ['processing', 'error'];

// listen to all events (without a specific type)
eventSource.onmessage = (event) => {
    const statuses = JSON.parse(event.data);

    for (const [fileId, status] of Object.entries(statuses)) {
        const el = document.querySelector(`.importFileEl[data-file-id="${fileId}"]`);

        if (!el) continue;

        for (const child of el.children) {

            let shouldDisable = disabledStatuses.includes(status);
            const viewLogsBtn = el.querySelector('.viewLogsBtn');
            const viewDataBtn = el.querySelector('.viewImportedDataBtn')

            if ('P' === child.tagName ) {
                child.className = `import-${status}`
                child.innerHTML = status;
            } 

            if ('processed' === status) {
                if (child.className === 'importBtn') {
                    child.setAttribute('disabled', '');
                    child.setAttribute('hidden', '');
                } else {
                    viewDataBtn.removeAttribute('disabled');
                    viewDataBtn.removeAttribute('hidden');
                }
            }

            else if ('importBtn' === child.className) {
                shouldDisable ? child.setAttribute('disabled', '') : '';

                if (child.hasAttribute('disabled')) {
                    child.style.cursor = 'not-allowed';
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