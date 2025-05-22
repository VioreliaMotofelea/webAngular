$(document).ready(function() {
    const forms = ['#addDestinationForm', '#editDestinationForm'];
    
    forms.forEach(formId => {
        $(formId).on('submit', function(e) {
            const location = $(this).find('#location').val().trim();
            const country = $(this).find('#country').val().trim();
            const costPerDay = $(this).find('#cost_per_day').val();
            
            let isValid = true;
            let errorMessage = '';
            
            if (location.length < 2) {
                isValid = false;
                errorMessage += 'Location must be at least 2 characters long.\n';
            }
            
            if (country.length < 2) {
                isValid = false;
                errorMessage += 'Country must be at least 2 characters long.\n';
            }
            
            if (isNaN(costPerDay) || costPerDay <= 0) {
                isValid = false;
                errorMessage += 'Cost per day must be a positive number.\n';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
            }
        });
    });
    
    $('input, textarea').on('input', function() {
        const input = $(this);
        const value = input.val().trim();
        
        if (input.attr('required') && value === '') {
            input.addClass('invalid');
        } else {
            input.removeClass('invalid');
        }
        
        if (input.attr('type') === 'number') {
            const numValue = parseFloat(value);
            if (isNaN(numValue) || numValue < 0) {
                input.addClass('invalid');
            } else {
                input.removeClass('invalid');
            }
        }
    });
}); 