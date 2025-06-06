<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}
$current_page = basename($_SERVER['PHP_SELF']);
// Redirect Field Managers to inventory
if ($_SESSION['admin_role'] === "Field Manager" && 
    !in_array($current_page, ['inventory.php', 'field-processing-order.php'])) {
    header('Location: field-processing-order.php');
    exit();
}?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard</title>
    <link rel="icon" href="assets/images/inventory.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
    <?php include "includes/link-css.php";?>

    <link rel="stylesheet" href="assets/css/inventory.css">
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <button class="mobile-menu-toggle" id="menuToggle">
            <i class="fa-solid fa-bars"></i>
        </button>

        <?php include "includes/sidebar.php";?>
    
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Main Content -->
        <main class="main">
        <header class="header">
                <h1 class="header-dashboard">Dashboard</h1>
                
                <div class="user-menu">
                <div class="theme-toggle" id="themeToggle" style="display:none;">
                <span style="margin-right:8px;" style="display:none;">Dark Mode</span>
                <i class="fas fa-moon"></i>
            </div>
                    
          <?php include "includes/notification.php";?>

    </div>
                    
                   <?php include "includes/profile.php";?>
                </div>
            </header>
            
            <!-- Table -->
            <section class="table-card fade-in">
                <div class="table-header">
                    <h3 class="table-title">Inventory Items</h3>
                    <div class="table-actions">
                        <button class="btn btn-outline">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <?php if ($_SESSION['admin_role'] === "Field Manager"): ?>
                        <button class="btn btn-primary" id="requestStocksBtn" disabled>
                            <i class="fas fa-paper-plane"></i>
                            <span>Request Selected Stocks</span>
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-primary" id="addItemBtn">
                            <i class="fas fa-plus"></i>
                            <span>Add Item</span>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <?php if ($_SESSION['admin_role'] === "Field Manager"): ?>
                                <th width="50px"><input type="checkbox" id="selectAllCheckbox"></th>
                                <?php endif; ?>
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
                
            </section>
        </main>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Add/Edit Item Modal -->
    <div class="modal" id="itemModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add Item</h3>
                <button class="modal-close" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="itemForm">
                    <input type="hidden" id="itemId">
                    <div class="form-group">
                        <label for="name">Item Name</label>
                        <input type="text" id="name" required placeholder="Enter item name">
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" required placeholder="Enter quantity">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline btn-danger modal-close">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content small">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <button class="modal-close" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item? This action cannot be undone.</p>
                <input type="hidden" id="deleteId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline modal-close">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete Item</button>
            </div>
        </div>
    </div>

    <!-- Request Stocks Modal -->
    <div class="modal" id="requestStocksModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Request Stocks</h3>
                <button class="modal-close" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="requestStocksForm">
                    <div id="requestItemsContainer">
                        <!-- Request items will be added here -->
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline btn-danger modal-close">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/toast.js"></script>
    
    <script>
        // DOM Elements
        const addItemBtn = document.getElementById('addItemBtn');
        const requestStocksBtn = document.getElementById('requestStocksBtn');
        const itemModal = document.getElementById('itemModal');
        const deleteModal = document.getElementById('deleteModal');
        const requestStocksModal = document.getElementById('requestStocksModal');
        const itemForm = document.getElementById('itemForm');
        const requestStocksForm = document.getElementById('requestStocksForm');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const modalCloses = document.querySelectorAll('.modal-close');
        const inventoryTableBody = document.getElementById('inventoryTableBody');
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const requestItemsContainer = document.getElementById('requestItemsContainer');

        // Selected items array
        let selectedItems = [];

        // Modal Functions
        function openModal(modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Event Listeners
        addItemBtn.addEventListener('click', () => {
            document.getElementById('modalTitle').textContent = 'Add Item';
            itemForm.reset();
            document.getElementById('itemId').value = '';
            openModal(itemModal);
        });

        if (requestStocksBtn) {
            requestStocksBtn.addEventListener('click', () => {
                // Clear previous items
                requestItemsContainer.innerHTML = '';
                
                // Add form fields for each selected item
                selectedItems.forEach(item => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'form-group';
                    itemDiv.innerHTML = `
                        <label>${item.name} (Current: ${item.quantity})</label>
                        <input type="hidden" name="item_ids[]" value="${item.id}">
                        <input type="number" name="quantities[]" min="1" required 
                               placeholder="Enter quantity to request" class="request-quantity">
                    `;
                    requestItemsContainer.appendChild(itemDiv);
                });
                
                openModal(requestStocksModal);
            });
        }

        modalCloses.forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                closeModal(modal);
            });
        });

        // Select/Deselect all items
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.item-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedItems();
            });
        }

        // Update selected items array
        function updateSelectedItems() {
            selectedItems = [];
            const checkboxes = document.querySelectorAll('.item-checkbox:checked');
            
            checkboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                selectedItems.push({
                    id: checkbox.value,
                    name: row.cells[<?php echo ($_SESSION['admin_role'] === "Field Manager") ? 1 : 0; ?>].textContent,
                    quantity: row.cells[<?php echo ($_SESSION['admin_role'] === "Field Manager") ? 2 : 1; ?>].textContent
                });
            });
            
            // Enable/disable request button based on selection
            if (requestStocksBtn) {
                requestStocksBtn.disabled = selectedItems.length === 0;
            }
        }

        // Load Items
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
                                <?php if ($_SESSION['admin_role'] === "Field Manager"): ?>
                                <td><input type="checkbox" class="item-checkbox" value="${item.id}"></td>
                                <?php endif; ?>
                                <td>${item.name}</td>
                                <td>${item.quantity}</td>
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

                        // Add event listeners
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

                        // Add checkbox event listeners for Field Manager
                        if (document.querySelectorAll('.item-checkbox')) {
                            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                                checkbox.addEventListener('change', updateSelectedItems);
                            });
                        }
                    } else {
                        showToast('Error', data.message || 'Failed to load items', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'Failed to load items', 'error');
                });
        }

        // Submit Stock Request
        if (requestStocksForm) {
            requestStocksForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData();
                const itemIds = document.querySelectorAll('input[name="item_ids[]"]');
                const quantities = document.querySelectorAll('input[name="quantities[]"]');
                
                // Add items to form data
                itemIds.forEach((idInput, index) => {
                    formData.append('item_ids[]', idInput.value);
                    formData.append('quantities[]', quantities[index].value);
                });
                
                // Add field manager ID
                formData.append('field_manager_id', <?php echo $_SESSION['admin_id']; ?>);
                
                fetch('api/request_stocks.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showToast('Success', 'Stock request submitted successfully', 'success');
                        closeModal(requestStocksModal);
                        // Uncheck all items
                        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                            checkbox.checked = false;
                        });
                        updateSelectedItems();
                    } else {
                        showToast('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'An error occurred', 'error');
                });
            });
        }

        // Edit Item
        function editItem(id) {
            fetch(`api/get_item.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('modalTitle').textContent = 'Edit Item';
                        document.getElementById('itemId').value = data.id;
                        document.getElementById('name').value = data.name;
                        document.getElementById('quantity').value = data.quantity;
                        openModal(itemModal);
                    } else {
                        showToast('Error', data.message || 'Failed to load item data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'Failed to load item data', 'error');
                });
        }

        // Save Item (Add/Edit)
        itemForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const id = document.getElementById('itemId').value;
            const name = document.getElementById('name').value;
            const quantity = document.getElementById('quantity').value;

            if (!name || quantity === '') {
                showToast('Error', 'All fields are required', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('name', name);
            formData.append('quantity', quantity);

            const url = id ? 'api/update_item.php' : 'api/add_item.php';

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    closeModal(itemModal);
                    loadItems();
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred', 'error');
            });
        });

        // Delete Item
        confirmDeleteBtn.addEventListener('click', function() {
            const id = document.getElementById('deleteId').value;

            fetch('api/delete_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    closeModal(deleteModal);
                    loadItems();
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred', 'error');
            });
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadItems();
        });
    </script>

    <?php include "includes/script-src.php";?>
</body>
</html>