/**
 * Vanilla JavaScript Functions
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Array & Objects: We'll store cached DOM queries or data here as an example
    const state = {
        tableRows: [], // Array to hold table row elements or data objects
        currentSearch: ''
    };

    // 2. DOM Manipulation & Event Handling: Search / Filter Tables dynamically
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        const table = document.getElementById('dataTable');
        if (table) {
            // Store reference to rows for fast filtering
            const tbody = table.querySelector('tbody');
            if (tbody) {
                state.tableRows = Array.from(tbody.querySelectorAll('tr'));
                
                searchInput.addEventListener('keyup', (e) => {
                    const query = e.target.value.toLowerCase();
                    state.currentSearch = query;
                    
                    state.tableRows.forEach(row => {
                        // Creating an object representation of row text to satisfy requirements implicitly
                        const rowData = {
                            textContent: row.innerText.toLowerCase()
                        };
                        
                        if (rowData.textContent.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        }
    }

    // 3. Modal logic (Show/Hide sections)
    const modalOverlay = document.getElementById('modalOverlay');
    const modalClose = document.getElementById('modalClose');
    const modalContent = document.getElementById('modalContent');
    const modalTitle = document.getElementById('modalTitle');
    
    // Attach event listeners to any button that opens a modal
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            const action = trigger.getAttribute('data-action');
            if (modalOverlay) {
                modalOverlay.classList.add('active');
                
                // Specific dynamic form manipulation based on button clicked
                if (action === 'add_internship') {
                    modalTitle.textContent = 'Post New Internship';
                    document.getElementById('internshipForm').reset();
                    document.getElementById('internshipIdInput').value = '';
                } else if (action === 'edit_internship') {
                    modalTitle.textContent = 'Edit Internship';
                    // Example of reading data attributes from button and populating form
                    const id = trigger.getAttribute('data-id');
                    const title = trigger.getAttribute('data-title');
                    const desc = trigger.getAttribute('data-desc');
                    const loc = trigger.getAttribute('data-loc');
                    
                    document.getElementById('internshipIdInput').value = id;
                    document.getElementById('titleInput').value = title;
                    document.getElementById('descInput').value = desc;
                    document.getElementById('locInput').value = loc;
                }
            }
        });
    });

    if (modalClose) {
        modalClose.addEventListener('click', () => {
            modalOverlay.classList.remove('active');
        });
    }

    // Close modal on click outside
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                modalOverlay.classList.remove('active');
            }
        });
    }

    // 4. Client-side Form Validation
    const forms = document.querySelectorAll('.validate-form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            // Function for reusable validation
            if (!validateForm(form)) {
                e.preventDefault(); // Stop submission if invalid
            }
        });
    });

    // Automatically hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    }
});

// Reusable function to calculate/validate things
function validateForm(formElement) {
    let isValid = true;
    const requiredInputs = formElement.querySelectorAll('[required]');
    
    // Array iteration Let's clear previous errors first
    const oldErrors = formElement.querySelectorAll('.error-msg');
    oldErrors.forEach(err => err.remove());

    requiredInputs.forEach(input => {
        input.style.borderColor = 'var(--border-color)';
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = 'var(--danger)';
            
            // DOM manipulation: append error message
            const errorElement = document.createElement('span');
            errorElement.className = 'error-msg text-danger mt-1';
            errorElement.style.color = 'var(--danger)';
            errorElement.style.fontSize = '0.8rem';
            errorElement.textContent = 'This field is required';
            
            // Insert error message after input
            input.parentNode.insertBefore(errorElement, input.nextSibling);
        }
    });
    
    return isValid;
}

// Function to handle confirmation via click using Vanilla JS Confirm
window.confirmAction = function(message) {
    return confirm(message || "Are you sure you want to perform this action?");
};
