<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Default table styles for desktop */
        .responsive-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .responsive-table th, 
        .responsive-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .responsive-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .btn-icon {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            margin: 0 3px;
            color: #6c757d;
            font-size: 16px;
        }
        
        .btn-icon:hover {
            color: #0d6efd;
        }
        
        /* Mobile card view styles */
        @media screen and (max-width: 768px) {
            .responsive-table {
                border: 0;
            }
            
            .responsive-table thead {
                display: none;
            }
            
            .responsive-table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 10px;
                background-color: #fff;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .responsive-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 10px;
                border-bottom: 1px solid #eee;
                border: 0;
            }
            
            .responsive-table td:before {
                content: attr(data-label);
                font-weight: bold;
                margin-right: 15px;
                flex: 1;
            }
            
            .responsive-table td:last-child {
                border-bottom: 0;
            }
            
            .responsive-table .actions {
                justify-content: flex-end;
                padding-top: 10px;
                border-top: 1px solid #eee;
                margin-top: 5px;
            }
            
            .responsive-table .actions:before {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="responsive-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="inventoryTableBody">
                <!-- Items will be loaded here via JavaScript -->
            </tbody>
        </table>
    </div>

    <script>
        // DOM Element
        const inventoryTableBody = document.getElementById('inventoryTableBody');

        function loadItems() {
            fetch('api/get_items.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        inventoryTableBody.innerHTML = '';
                        data.data.forEach(item => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td data-label="Item Name">${item.name}</td>
                                <td data-label="Quantity">${item.quantity}</td>
                                <td class="actions">
                                    <button class="btn-icon edit-item" data-id="${item.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon delete-item" data-id="${item.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            `;
                            inventoryTableBody.appendChild(row);
                        });

                        // Add event listeners to edit and delete buttons
                        document.querySelectorAll('.edit-item').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const itemId = this.getAttribute('data-id');
                                editItem(itemId);
                            });
                        });

                        document.querySelectorAll('.delete-item').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const itemId = this.getAttribute('data-id');
                                document.getElementById('deleteId').value = itemId;
                                openModal(deleteModal);
                            });
                        });
                    } else {
                        showToast('Error', data.message || 'Failed to load items', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'Failed to load items', 'error');
                });
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadItems();
        });

        // Your existing functions (keep these as they are in your original code)
        function editItem(itemId) {
            // Your existing editItem implementation
        }

        function showToast(title, message, type) {
            // Your existing toast implementation
        }

        function openModal(modal) {
            // Your existing modal implementation
        }
    </script>
</body>
</html>