    // View full image function
    function viewFullImage() {
        const imgSrc = document.getElementById('modalDesign').src;
        const modal = document.getElementById('imageViewModal');
        const fullImg = document.getElementById('fullImageView');
        
        fullImg.src = imgSrc;
        modal.style.display = "block";
        
        // Close modal when clicking X or outside
        document.querySelector('.close-image-modal').onclick = function() {
            modal.style.display = "none";
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }
    
    function downloadDesign() {
        const imgSrc = document.getElementById('modalDesign').src;
        const customerName = document.getElementById('modalName').textContent;
        const printType = document.getElementById('modalPrintType').textContent;
        const ticket = document.getElementById('modalTicket').textContent;
    
        // Clean up the filename
        const cleanName = customerName.replace(/[^a-z0-9]/gi, '_').toLowerCase();
        const cleanPrintType = printType.replace(/[^a-z0-9]/gi, '_').toLowerCase();
        const cleanTicket = ticket.replace(/[^a-z0-9]/gi, '_').toLowerCase();
        const fileName = `${cleanTicket}-${cleanName}-${cleanPrintType}.png`;
    
        // Create a temporary link
        const link = document.createElement('a');
        link.href = imgSrc;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }