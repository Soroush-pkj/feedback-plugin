document.addEventListener('DOMContentLoaded', function () {
    // Star rating interaction
    const stars = document.querySelectorAll('.star');
    
    stars.forEach(star => {
        star.addEventListener('mouseover', function () {
            const value = this.getAttribute('data-value');
            this.classList.add('hover');
            let nextSibling = this.nextElementSibling;
            while (nextSibling) {
                nextSibling.classList.add('hover');
                nextSibling = nextSibling.nextElementSibling;
            }
            let previousSibling = this.previousElementSibling;
            while (previousSibling) {
                previousSibling.classList.remove('hover');
                previousSibling = previousSibling.previousElementSibling;
            }
        });

        star.addEventListener('click', function () {
            const value = this.getAttribute('data-value');
            document.getElementById('rating').value = value;
            this.classList.add('selected');
            let nextSibling = this.nextElementSibling;
            while (nextSibling) {
                nextSibling.classList.add('selected');
                nextSibling = nextSibling.nextElementSibling;
            }
            let previousSibling = this.previousElementSibling;
            while (previousSibling) {
                previousSibling.classList.remove('selected');
                previousSibling = previousSibling.previousElementSibling;
            }
        });
    });

    const feedbackForm = document.getElementById('feedback-form');
    feedbackForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(feedbackForm);
        formData.append('action', 'submit_feedback');

        fetch(mfp_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const feedbackResponse = document.getElementById('feedback-response');
            if (data.success) {
                feedbackResponse.innerHTML = '<p>' + data.data.message + '</p>';
                feedbackForm.reset();
                document.querySelectorAll('.star').forEach(star => star.classList.remove('selected'));
            } else {
                feedbackResponse.innerHTML = '<p>' + data.data.message + '</p>';
            }
        })
        .catch(error => console.error('Error:', error));
    });
});



// form error validation 
const form = document.getElementById('feedback-form');
const errorContainer = document.createElement('div');
errorContainer.style.color = 'red';
form.insertAdjacentElement('beforebegin', errorContainer);

const isEnglishText = (text) => /^[a-zA-Z0-9 .,!?'-]*$/.test(text);

const validateField = (field) => {
    const fieldName = field.getAttribute('id');
    let error = '';

    if (!field.value.trim()) {
        error = `${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)} is required.`;
    } else if (fieldName === 'email') {
        const emailRegex = /^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,7}$/;
        if (!emailRegex.test(field.value.trim())) {
            error = 'Please enter a valid email address.';
        }
    } else if (['name', 'feedback'].includes(fieldName)) {
        if (!isEnglishText(field.value.trim())) {
            error = `${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)} must be in English.`;
        }
    }

    // Display error for the specific field
    let errorElement = field.nextElementSibling;
    if (!errorElement || !errorElement.classList.contains('error-message')) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.style.color = 'red';
        field.insertAdjacentElement('afterend', errorElement);
    }
    errorElement.textContent = error;

    return error === '';
};

form.querySelectorAll('input, textarea').forEach((field) => {
    field.addEventListener('input', () => validateField(field));
});


form.addEventListener('submit', (event) => {
    let isValid = true;
    form.querySelectorAll('input, textarea').forEach((field) => {
        if (!validateField(field)) {
            isValid = false;
        }
    });

    if (!isValid) {
        event.preventDefault();
    }
});
