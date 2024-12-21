document.addEventListener('DOMContentLoaded', function () {
    const stars = document.querySelectorAll('.star');

    stars.forEach(star => {
        star.addEventListener('mouseover', function () {
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

    // validation form
    const feedbackForm = document.getElementById('feedback-form');

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
                error = `${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)} has invalid text type, it must be simple text in English.`;
            }
        }

        // show errors
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

    feedbackForm.querySelectorAll('input, textarea').forEach((field) => {
        field.addEventListener('input', () => validateField(field));
    });

    feedbackForm.addEventListener('submit', (event) => {
        event.preventDefault(); 

        let isValid = true;
        feedbackForm.querySelectorAll('input, textarea').forEach((field) => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        if (!isValid) {
            return;
        }

        // Ajax form submit
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
                    feedbackResponse.innerHTML = '<p style="color:red">' + data.data.message + '</p>';
                }
            })
            .catch(error => console.error('Error:', error));
    });
});
