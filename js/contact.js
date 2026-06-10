$(document).ready(function(){

  // Bootstrap validation
  function validateForm(form) {
    let isValid = true;
    form.find('input, textarea').each(function(){
      if (!this.checkValidity()) {
        $(this).addClass('is-invalid');
        isValid = false;
      } else {
        $(this).removeClass('is-invalid');
      }
    });
    return isValid;
  }

  $('#contactForm').submit(function(e){
    e.preventDefault();

    let form = $(this);

    if (!validateForm(form)) {
      return;
    }

    $.ajax({
      url: 'php/contact-process.php',
      type: 'POST',
      data: form.serialize(),
      success: function(response){
        $('#formAlert').html('<div class="alert alert-success">'+response+'</div>');
        form[0].reset();
        form.find('.is-invalid').removeClass('is-invalid');
      },
      error: function(){
        $('#formAlert').html('<div class="alert alert-danger">There was an error. Please try again later.</div>');
      }
    });
  });

});
