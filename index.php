<?php session_start(); ?>
<?php include 'header.php'; ?>
<body class="hold-transition login-page" style="background-image: url('./images/coffee1.jpg'); background-repeat: no-repeat, repeat; background-size: cover;">
<div class="login-box">
    <div class="login-logo">
  <p id="date"></p>
  <p id="time" class="bold"></p>
</div>

<!-- Floating Alerts -->
<div id="alert-container">
  <div class="alert alert-success alert-dismissible custom-alert" style="display:none;">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <span class="result"><i class="icon fa fa-check"></i> <span class="message"></span></span>
  </div>

  <div class="alert alert-danger alert-dismissible custom-alert" style="display:none;">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <span class="result"><i class="icon fa fa-warning"></i> <span class="message"></span></span>
  </div>
</div>

  
    <div class="login-box-body">
      <h4 class="login-box-msg">Enter Employee ID or Scan QR Code</h4>

        <!-- QR SCANNER VIDEO FEED -->
        <div class="form-group text-center">
            <label>Scan your QR Code:</label>
            <video id="preview" width="100%" style="border: 1px solid #ccc; border-radius: 5px; z-index: 1;"></video>

        </div>

      <form id="attendance" action="" method="POST">
            <div class="form-group">
                <select class="form-control" name="status">
                    <option value="in">Time In</option>
                    <option value="out">Time Out</option>
                    <option value="breakin">Break In</option>
                    <option value="breakout">Break Out</option>
                </select>
            </div>

          <div class="form-group has-feedback">
            <input type="text" class="form-control input-lg" id="employee" name="employee" required placeholder="Enter or Scan Employee ID">
            <input type="hidden" name="generated_code" id="generated_code">


            <span class="glyphicon glyphicon-calendar form-control-feedback"></span>
          </div>

          <div class="row">
          <div class="col-xs-4">
                <button type="submit" class="btn btn-primary btn-block btn-flat" name="signin"><i class="fa fa-sign-in"></i> Sign In</button>
            </div>
          </div>
      </form>
    </div>

  <!-- ALERTS -->


<?php include 'scripts.php' ?>

<!-- MOMENT.JS CLOCK -->
<script type="text/javascript">
$(function() {
    var interval = setInterval(function() {
        var momentNow = moment();
        $('#date').html(momentNow.format('dddd').substring(0,3).toUpperCase() + ' - ' + momentNow.format('MMMM DD, YYYY'));  
        $('#time').html(momentNow.format('hh:mm:ss A'));
    }, 100);

    $('#attendance').submit(function(e){
        e.preventDefault();
        var attendance = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: 'attendance.php',
            data: attendance,
            dataType: 'json',
            success: function(response){
    $('.alert').hide();

    if(response.error){
        $('.alert-danger').show();
        $('.message').html(response.message);
    } else {
        $('.alert-success').show();
        $('.message').html(response.message);
    }

    $('#employee').val('');

    // Reload the page after 3 seconds in ALL cases
    setTimeout(() => location.reload(), 3000);
}


        });
    });
});
</script>

<!-- INSTASCAN JS (QR SCANNER) -->
<!-- INSTASCAN JS (QR SCANNER) -->
<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>

<script>
  $(document).ready(function () {
    const videoElement = document.getElementById('preview');

    let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });

    scanner.addListener('scan', function (content) {
  console.log('[SCANNED]', content);

  // Send AJAX request to get employee_id
  $.ajax({
    type: 'POST',
    url: 'lookup_employee.php',
    data: { generated_code: content },
    dataType: 'json',
    success: function (response) {
  console.log('Lookup response:', response); // ADD THIS
  if (response.success) {
    $('#employee').val(response.employee_id);
    $('#generated_code').val(content);
    $('#attendance').submit();
  } else {
    alert(response.message || 'Employee not found!');
  }
}
,
    error: function (xhr, status, error) {
  console.error('AJAX Error:', status, error);
  console.log('Response:', xhr.responseText); // See what's coming back
  alert('Something went wrong while fetching employee info.');
}

  });
});


    Instascan.Camera.getCameras().then(function (cameras) {
      if (cameras.length > 0) {
        scanner.start(cameras[0]);
        console.log('[INFO] Scanner started...');
      } else {
        alert('No cameras found.');
      }
    }).catch(function (e) {
      console.error('[ERROR] Camera issue:', e);
    });
  });
</script>





</body>
</html>
