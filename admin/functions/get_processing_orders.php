<?php
require_once '../../db_connection.php';
require_once 'auth_check.php';

if (!isLoggedIn()) {
    die(json_encode(['error' => 'Unauthorized access']));
}

// Fetch only orders with status 'processing'
$sql = "SELECT orders.*, users.name 
        FROM orders 
        INNER JOIN users ON orders.user_id = users.id 
        WHERE orders.status = 'processing' AND orders.is_for_processing ='no'
        ORDER BY orders.created_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($order = $result->fetch_assoc()) {
        // Determine the appropriate thumbnail based on file extension
        $designFile = $order['design_file'];
        $fileExtension = strtolower(pathinfo($designFile, PATHINFO_EXTENSION));
        
        if ($fileExtension === 'psd') {
            $thumbnail = "../photoshop.png";
        } elseif ($fileExtension === 'pdf') {
            $thumbnail = "../pdf.png";
        } elseif ($fileExtension === 'ai') {
            $thumbnail = "../illustrator.png";
        } else {
            // For image files, use the actual file
            $thumbnail = "../user/" . htmlspecialchars($designFile, ENT_QUOTES, 'UTF-8');
        }
        
        echo '<tr>
                <td>'.htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8').'</td>
                <td>
                    <div class="user-cell">
                        <img src="'.$thumbnail.'" alt="file design" width="50" height="50">
                        <span>'.htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8').'</span>
                    </div>
                </td>
                <td>'.htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8').'</td>
                <td>
                    <span class="status status-warning">
                        '.htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8').'
                    </span>
                </td>
                <td>
                    <button class="btn btn-outline view-quote-modal" 
                            data-id="'.htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8').'"
                            data-ticket="'.htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8').'"
                            data-design="'.htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8').'"
                            data-print-type="'.htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8').'"
                            data-quantity="'.htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8').'">
                        Process
                    </button>
                </td>
            </tr>';
    }
} else {
    echo '<tr><td colspan="7">No orders currently for processing</td></tr>';
}

$conn->close();
?>