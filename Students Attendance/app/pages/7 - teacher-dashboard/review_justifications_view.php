<!DOCTYPE html>
<html lang="en" data-theme="light"> <!-- Use theme from localStorage -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Justifications</title>
    <!-- Reuse teacher dashboard CSS for consistency -->
    <link rel="stylesheet" href="teacher-dashboard.css">
     <style>
         /* Add specific styles if needed */
        .justification-text {
            white-space: pre-wrap; /* Preserve line breaks in justification */
            max-height: 100px; /* Limit initial display height */
            overflow-y: auto; /* Add scroll if text is long */
            background-color: var(--bg-color);
            padding: 8px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            margin-top: 5px;
            font-size: 0.9em;
        }
         .actions button {
             padding: 5px 10px;
             margin: 0 5px;
             border: none;
             border-radius: 4px;
             cursor: pointer;
             font-size: 0.9em;
         }
        .actions .accept-btn { background-color: #28a745; color: white; }
        .actions .reject-btn { background-color: #dc3545; color: white; }
        .actions .accept-btn:hover { background-color: #218838; }
        .actions .reject-btn:hover { background-color: #c82333; }
         #status-message { margin-top: 15px; }
         .dashboard-container { display: block; } /* Override flex for this simple page */
         .main-content { margin-left: 0; padding: 30px;}
         .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;}
         .back-link { text-decoration: none; color: #007bff; }
         .back-link:hover { text-decoration: underline;}
         table { margin-top: 20px; }
     </style>
</head>
<body>
    <div class="dashboard-container">
        <main class="main-content">
            <div class="page-header">
                 <h1>Review Pending Justifications</h1>
                 <a href="teacher-dashboard.html" class="back-link">Back to Dashboard</a>
            </div>

            <div id="status-message"></div> <!-- For overall status like loading/errors -->

            <section class="justifications-list">
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Absence Date</th>
                            <th>Justification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="justifications-tbody">
                        <!-- Justifications will be loaded here -->
                        <tr><td colspan="4">Loading justifications...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        const tbody = document.getElementById('justifications-tbody');
        const statusDiv = document.getElementById('status-message');

        async function fetchJustifications() {
            statusDiv.textContent = 'Loading...';
            statusDiv.style.color = 'blue';
            tbody.innerHTML = '<tr><td colspan="4">Loading justifications...</td></tr>'; // Clear and show loading

            try {
                const response = await fetch('get_justifications.php');
                if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
                const data = await response.json();

                if (data.success) {
                    if (data.justifications.length > 0) {
                        tbody.innerHTML = ''; // Clear loading message
                        data.justifications.forEach(item => {
                            const row = document.createElement('tr');
                            row.setAttribute('data-record-id', item.record_id); // Store record ID on the row
                            row.innerHTML = `
                                <td>${item.student_name}</td>
                                <td>${item.absence_date}</td>
                                <td><div class="justification-text">${item.justification}</div></td>
                                <td class="actions">
                                    <button class="accept-btn" onclick="updateJustification(${item.record_id}, 'accepted', this)">Accept</button>
                                    <button class="reject-btn" onclick="updateJustification(${item.record_id}, 'rejected', this)">Reject</button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                         statusDiv.textContent = ''; // Clear loading text on success
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4">No pending justifications found for your group.</td></tr>';
                         statusDiv.textContent = ''; // Clear loading text
                    }
                } else {
                    throw new Error(data.error || 'Failed to load justifications.');
                }
            } catch (error) {
                console.error("Fetch Justifications Error:", error);
                tbody.innerHTML = `<tr><td colspan="4" style="color: red;">Error: ${error.message}</td></tr>`;
                statusDiv.textContent = `Error loading: ${error.message}`;
                statusDiv.style.color = 'red';
            }
        }

        async function updateJustification(recordId, newStatus, buttonElement) {
             // Disable buttons to prevent double clicks
             const actionButtons = buttonElement.parentNode.querySelectorAll('button');
             actionButtons.forEach(btn => btn.disabled = true);
             buttonElement.textContent = 'Processing...';

             statusDiv.textContent = ''; // Clear previous status

            try {
                const formData = new FormData();
                formData.append('record_id', recordId);
                formData.append('new_status', newStatus);

                const response = await fetch('update_justification.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
                const data = await response.json();

                if (data.success) {
                    // Remove the row from the table visually on success
                    const row = tbody.querySelector(`tr[data-record-id="${recordId}"]`);
                    if (row) {
                         row.style.transition = 'opacity 0.5s ease-out';
                         row.style.opacity = '0';
                         setTimeout(() => {
                             row.remove();
                             // Check if table is empty after removal
                            if (tbody.children.length === 0) {
                                 tbody.innerHTML = '<tr><td colspan="4">No pending justifications found for your group.</td></tr>';
                            }
                         }, 500);
                    }
                    statusDiv.textContent = `Justification ${newStatus} successfully.`;
                    statusDiv.style.color = 'green';
                } else {
                    throw new Error(data.error || `Failed to update status to ${newStatus}.`);
                }

            } catch (error) {
                console.error("Update Justification Error:", error);
                statusDiv.textContent = `Error updating: ${error.message}`;
                statusDiv.style.color = 'red';
                 // Re-enable buttons on error
                 actionButtons.forEach(btn => btn.disabled = false);
                 buttonElement.textContent = newStatus === 'accepted' ? 'Accept' : 'Reject'; // Reset button text
            }
        }

         // Initialize theme and fetch data on load
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            // Add theme toggle button logic if needed, similar to teacher-dashboard

            fetchJustifications();
        });
    </script>
</body>
</html>