
    const fileInput = document.getElementById('file');
    const previewContainer = document.getElementById('image-preview-container');

    fileInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                // Create image preview element
                const imgPreview = document.createElement('img');
                imgPreview.src = e.target.result;
                imgPreview.classList.add('w-32', 'h-32', 'object-cover', 'rounded-md');

                // Create remove button
                const removeButton = document.createElement('button');
                removeButton.textContent = '×';
                removeButton.classList.add('absolute', 'top-0', 'right-0', 'bg-red-500', 'text-white', 'w-6', 'h-6', 'flex', 'items-center', 'justify-center', 'rounded-full', 'cursor-pointer', 'z-10');

                // Create container for the preview image
                const imageContainer = document.createElement('div');
                imageContainer.classList.add('relative', 'w-32', 'h-32'); // Make sure the container has relative positioning
                imageContainer.appendChild(imgPreview);
                imageContainer.appendChild(removeButton);

                // Append preview to the container
                previewContainer.innerHTML = ''; // Clear any previous preview
                previewContainer.appendChild(imageContainer);

                // Remove image preview when the remove button is clicked
                removeButton.addEventListener('click', function() {
                    previewContainer.innerHTML = ''; // Remove the preview container
                    fileInput.value = ''; // Reset the file input
                });
            };

            reader.readAsDataURL(file);
        }
    });
