    // Image Viewer Modal
    const imageViewerModal = document.createElement('div');
imageViewerModal.className = 'image-viewer-modal';
imageViewerModal.innerHTML = `
  <span class="close-viewer">&times;</span>
  <img class="image-viewer-content" id="viewed-image">
`;
document.body.appendChild(imageViewerModal);

// View button functionality
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('view-design-btn')) {
    const imgSrc = e.target.closest('.design-image-container').querySelector('img').src;
    document.getElementById('viewed-image').src = imgSrc;
    imageViewerModal.style.display = 'block';
  }
});

// Close button functionality
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('close-viewer')) {
    imageViewerModal.style.display = 'none';
  }
});

// Close viewer when clicking outside the image
imageViewerModal.addEventListener('click', function (e) {
  if (e.target === imageViewerModal) {
    imageViewerModal.style.display = 'none';
  }
});

// Download button functionality
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('download-design-btn')) {
    const container = e.target.closest('.design-image-container');
    const imgSrc = container.querySelector('img').src;
    const ticket = document.getElementById('quote-modal-ticket').textContent;
    const printType = document.getElementById('quote-modal-print-type').textContent;
    
    // Extract filename from URL
    const filename = imgSrc.split('/').pop();
    const extension = filename.split('.').pop();
    
    // Create download link
    const link = document.createElement('a');
    link.href = imgSrc;
    link.download = `${ticket}-${printType.toLowerCase().replace(/ /g, '-')}.${extension}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
});

// Close image viewer
document.querySelector('.close-viewer').onclick = function() {
  imageViewerModal.style.display = 'none';
}

// Close when clicking outside image
imageViewerModal.onclick = function(e) {
  if (e.target === imageViewerModal) {
    imageViewerModal.style.display = 'none';
  }
}