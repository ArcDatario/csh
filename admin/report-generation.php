   <!-- Add this near the theme toggle -->
            <div class="report-button">
                <button class="btn btn-primary" id="generateReportBtn">
                    <i class="fas fa-file-export"></i> Generate Report
                </button>
            </div>

            <!-- Report Generation Modal -->
            <div class="modal" id="reportModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Generate Report</h3>
                        <button class="modal-close" id="closeReportModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="reportForm">
                            <div class="form-group">
                                <label for="startDate" class="form-label">Start Date</label>
                                <input type="date" id="startDate" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" id="endDate" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="reportType" class="form-label">Report Type</label>
                                <select id="reportType" class="form-control" required>
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline" id="cancelReport">Cancel</button>
                        <button class="btn btn-primary" id="generateReport">Generate</button>
                    </div>
                </div>
            </div>

            <script>
                // Report Generation
                document.getElementById('generateReportBtn').addEventListener('click', function() {
                    document.getElementById('reportModal').classList.add('active');
                });

                document.getElementById('closeReportModal').addEventListener('click', function() {
                    document.getElementById('reportModal').classList.remove('active');
                });

                document.getElementById('cancelReport').addEventListener('click', function() {
                    document.getElementById('reportModal').classList.remove('active');
                });

                document.getElementById('generateReport').addEventListener('click', function() {
                    const startDate = document.getElementById('startDate').value;
                    const endDate = document.getElementById('endDate').value;
                    const reportType = document.getElementById('reportType').value;
                    
                    if (!startDate || !endDate) {
                        alert('Please select both start and end dates');
                        return;
                    }
                    
                    // Generate the report
                    window.open(`generate_report.php?start_date=${startDate}&end_date=${endDate}&type=${reportType}`, '_blank');
                    
                    // Close the modal
                    document.getElementById('reportModal').classList.remove('active');
                });
            </script>

            <style>
                /* Report Button */
.report-button {
    margin-right: 15px;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    max-width: 90%;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
}

.modal-body {
    margin-bottom: 15px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
            </style>