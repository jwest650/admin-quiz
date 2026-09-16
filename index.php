<?php
// if (file_exists('install/index.php')) {
//     header("location:install/");
//     die();
// }
session_start();
if (isset($_SESSION['username'])) {
    header("location:home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <!-- Meta, title, CSS, favicons, etc. -->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login | Quiz</title>
  <?php include 'include-css.php'; ?>
 </head>

 <body class="login">
  <div>
   <a class="hiddenanchor" id="signup"></a>
   <a class="hiddenanchor" id="signin"></a>
   <div class="login_wrapper">
    <div class="animate form login_form">
     <section class="login_content">
      <form method="POST" id="login_form" action="check_login.php">
       <h1 class="text-center">LOGIN</h1>
       <div>
        <input type="text" id="username" name="username" class="form-control" placeholder="Username" autocomplete="username" autocapitalize="none" spellcheck="false" required />
       </div>
       <div>
        <input type="password" id="password" name="password" class="form-control" placeholder="Password" autocomplete="current-password" required />
       </div>
       <div class="row mt-20">
        <input type="submit" id="login_button" class="btn btn-default text-center" value="Log in">
       </div>
       <div class="clearfix"></div>
       <div style="display:none;" id="result">
       </div>
       <div class="separator">
        <div class="clearfix"></div>
        <br>
        <img src="images/logo-460x114.png" alt="QUIZ" width='300'>
        <p class="text-center"><br>© <?= date('Y') ?> WRTeam</p>
       </div>
      </form>
     </section>
    </div>
   </div>
  </div>
  <!-- Including Jquery so All js Can run -->
  <script type="text/javascript" src="js/jquery.min.js"></script>
  <!-- Validadtion js -->
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
  <script>
  $("#login_form").validate({
   rules: {
    username: "required",
    password: "required",
   },
   messages: {
    username: {
     required: "Please Enter username"
    },
    password: {
     required: "Please Enter password",

    },
   }
  });
  var $button = $('#login_button');
  var $result = $('#result');

  // Escape before injecting, so a server message is never treated as markup.
  function escapeHtml(text) {
   return $('<div>').text(text).html();
  }

  function showAlert(message) {
   $result.html("<p class='alert alert-danger' style='margin-bottom:0'>" + escapeHtml(message) + "</p>").show();
  }

  function startLoader() {
   // <input type="submit"> has no inner HTML - its label is the value.
   $button.prop('disabled', true).val('Please Wait...');
   $result.hide().empty();
  }

  function stopLoader() {
   $button.prop('disabled', false).val('Log in');
  }

  // Turns any failed request into a message that actually describes what happened.
  function messageForFailure(jqXHR, textStatus) {
   // The endpoint sends {"success":false,"message":"..."} even on 4xx/5xx,
   // so prefer whatever it told us over a guess based on the status code.
   var payload = jqXHR.responseJSON;
   if (!payload && jqXHR.responseText) {
    try {
     payload = JSON.parse(jqXHR.responseText);
    } catch (err) {
     payload = null;
    }
   }
   if (payload && payload.message) {
    return payload.message;
   }

   if (textStatus === 'timeout') {
    return 'The server took too long to respond. Please try again.';
   }
   if (jqXHR.status === 0) {
    return 'No connection to the server. Check your internet and try again.';
   }
   if (textStatus === 'parsererror') {
    return 'The server sent an unexpected response. Please try again, or contact an administrator.';
   }
   if (jqXHR.status >= 500) {
    return 'The server had a problem handling the login. Please try again in a moment.';
   }
   return 'Login failed (error ' + jqXHR.status + '). Please try again.';
  }

  $(document).on('submit', '#login_form', function(e) {
   e.preventDefault();
   var $form = $(this);
   if (!$form.valid() || $button.prop('disabled')) {
    return;
   }
   $.ajax({
    url: $form.attr('action'),
    type: "POST",
    dataType: "json",
    data: $form.serialize(),
    timeout: 20000, // without this a hung request leaves the loader spinning forever
    beforeSend: startLoader,
    success: function(response) {
     if (response && response.success) {
      // Keep the loader running - we are navigating away.
      window.location.href = "home.php";
      return;
     }
     stopLoader();
     showAlert((response && response.message) || 'Username or password is incorrect.');
    },
    error: function(jqXHR, textStatus) {
     stopLoader();
     showAlert(messageForFailure(jqXHR, textStatus));
    }
   });
  });
  </script>
 </body>

</html>