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
  <title>Admin Login | UQuiz</title>
  <?php include 'include-css.php'; ?>
 </head>

 <body class="login-page">
  <main class="login-shell">
   <section class="login-brand" aria-hidden="true">
    <div class="login-brand__inner">
     <p class="login-brand__eyebrow">UQuiz Admin</p>
     <h2>Run every quiz from one place.</h2>
     <ul>
      <li><em class="fas fa-book"></em> Quiz zones, maths and exams</li>
      <li><em class="fas fa-child"></em> Junior quizzes and Guess the Word</li>
      <li><em class="fas fa-trophy"></em> Contests and leaderboards</li>
     </ul>
    </div>
   </section>
   <section class="login-panel">
    <div class="login-card">
     <img class="login-card__logo" src="images/logo-460x114.png" alt="UQuiz">
     <h1>Welcome back</h1>
     <p class="login-card__lead">Sign in to the admin panel.</p>
     <form method="POST" id="login_form" action="check_login.php" novalidate>
      <div class="login-field">
       <label for="username">Username</label>
       <div class="login-input">
        <em class="fas fa-user" aria-hidden="true"></em>
        <input type="text" id="username" name="username" class="form-control" placeholder="Your username" autocomplete="username" autocapitalize="none" spellcheck="false" required />
       </div>
      </div>
      <div class="login-field">
       <label for="password">Password</label>
       <div class="login-input">
        <em class="fas fa-lock" aria-hidden="true"></em>
        <input type="password" id="password" name="password" class="form-control" placeholder="Your password" autocomplete="current-password" required />
        <button type="button" class="login-reveal" id="password_reveal" aria-label="Show password" aria-pressed="false"><em class="fas fa-eye"></em></button>
       </div>
      </div>
      <div style="display:none;" id="result" role="alert"></div>
      <input type="submit" id="login_button" class="btn btn-primary login-submit" value="Log in">
     </form>
     <p class="login-card__foot">&copy; <?= date('Y') ?> UQuiz &middot; WRTeam</p>
    </div>
   </section>
  </main>
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

  // Show or hide the password.
  $('#password_reveal').on('click', function() {
   var $input = $('#password');
   var show = $input.attr('type') === 'password';
   $input.attr('type', show ? 'text' : 'password');
   $(this).attr({ 'aria-pressed': show, 'aria-label': show ? 'Hide password' : 'Show password' })
    .find('em').toggleClass('fa-eye', !show).toggleClass('fa-eye-slash', show);
  });
  </script>
 </body>

</html>