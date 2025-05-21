/**
 * Multi-step form functionality
 * 
 * This script handles the multi-step form navigation for the project creation/editing form.
 * It manages the step transitions, button visibility, and form validation.
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('multi-step-form');
    if (!form) return; // Exit if form doesn't exist on the page
    
    const stepButtons = document.querySelectorAll('.step-button');
    const stepContents = document.querySelectorAll('.step-content');
    const prevButton = document.getElementById('prev-button');
    const nextButton = document.getElementById('next-button');
    const submitButton = document.getElementById('submit-button');
    
    let currentStep = 1;
    const totalSteps = stepContents.length;

    /**
     * Shows the specified step and updates navigation buttons
     * @param {number} stepNumber - The step number to display
     */
    function showStep(stepNumber) {
        // Hide all steps
        stepContents.forEach(content => {
            content.classList.add('hidden');
        });
        
        // Show the current step
        document.getElementById(`step-${stepNumber}`).classList.remove('hidden');
        
        // Update step buttons
        stepButtons.forEach(button => {
            const buttonStep = parseInt(button.getAttribute('data-step'));
            
            button.classList.remove('active-step', 'border-indigo-500', 'text-indigo-600');
            button.classList.add('border-transparent', 'text-gray-500');
            
            const numSpan = button.querySelector('span');
            numSpan.classList.remove('bg-indigo-500');
            numSpan.classList.add('bg-gray-300');
            
            if (buttonStep === stepNumber) {
                button.classList.add('active-step', 'border-indigo-500', 'text-indigo-600');
                button.classList.remove('border-transparent', 'text-gray-500');
                
                numSpan.classList.add('bg-indigo-500');
                numSpan.classList.remove('bg-gray-300');
            }
        });
        
        // Update navigation buttons
        if (stepNumber === 1) {
            prevButton.classList.add('hidden');
        } else {
            prevButton.classList.remove('hidden');
        }
        
        if (stepNumber === totalSteps) {
            nextButton.classList.add('hidden');
            submitButton.classList.remove('hidden');
        } else {
            nextButton.classList.remove('hidden');
            submitButton.classList.add('hidden');
        }
        
        // Update current step
        currentStep = stepNumber;
    }
    
    // Event listeners for step buttons
    stepButtons.forEach(button => {
        button.addEventListener('click', function() {
            const stepNumber = parseInt(this.getAttribute('data-step'));
            showStep(stepNumber);
        });
    });
    
    // Event listener for previous button
    prevButton.addEventListener('click', function() {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    });
    
    // Event listener for next button
    nextButton.addEventListener('click', function() {
        if (currentStep < totalSteps) {
            showStep(currentStep + 1);
        }
    });
    
    // Initialize the form
    showStep(1);
});