/**
 * SPARKIIT Enrollment Form – Client-side validation
 */
(function () {
  'use strict';

  const form = document.getElementById('enrollmentForm');
  if (!form) return;

  form.addEventListener('submit', function (e) {
    let valid = true;

    // Bootstrap native validation
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
      valid = false;
    }

    // Custom: a course must be selected (radio)
    const courseSelected = document.querySelector('.course-radio:checked');
    const courseError    = document.getElementById('courses_error');
    if (!courseSelected) {
      e.preventDefault();
      e.stopPropagation();
      valid = false;
      if (courseError) courseError.textContent = 'Please select a course.';
    } else {
      if (courseError) courseError.textContent = '';
    }

    // Custom: total_program required
    const programSelected = document.querySelector('input[name="total_program"]:checked');
    if (!programSelected) {
      e.preventDefault();
      e.stopPropagation();
      valid = false;
    }

    form.classList.add('was-validated');

    // Disable submit + show spinner on valid submit
    if (valid && form.checkValidity() && courseSelected && programSelected) {
      const btn = document.getElementById('submitBtn');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
      }
    }
  });

  // Clear course error when user picks a radio
  document.querySelectorAll('.course-radio').forEach(function (rb) {
    rb.addEventListener('change', function () {
      const courseError = document.getElementById('courses_error');
      if (courseError) courseError.textContent = '';
    });
  });

})();
