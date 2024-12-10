
/* code for generating poll URL & call php */
async function generatePollURL() {
    if (availableSlots.length === 0) {
        alert("Please add at least one date and time slot before generating the poll URL.");
        return;
    }

    const pollTitle = document.getElementById('poll-title').value;

    if (!pollTitle) {
        alert("Please provide a title for the poll.");
        return;
    }

    // Prepare the payload
    const payload = {
        pollTitle: pollTitle,
        availableSlots: availableSlots.map(slot => {
            const [date, timeRange] = slot.split(": ");
            const [startTime, endTime] = timeRange.split(" - ");
            return {
                start_date: date,
                end_date: date, // Assuming same date for now
                duration: document.getElementById('meeting-length').value,
                start_time: startTime,
                end_time: endTime
            };
        })
    };

    try {
        const response = await fetch('../database_stuff/create_poll.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.status === 'success') {
            document.getElementById('poll-url').innerText = result.poll_url;
            document.getElementById('poll-result').classList.remove('hidden');
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('An unexpected error occurred. Please try again later.');
        console.error('Poll creation error:', error);
    }
}
