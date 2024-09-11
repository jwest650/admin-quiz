<?php
session_start()
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badges</title>
    <?php include 'include-css.php'; ?>

</head>
<body class="nav-md">
    <div class="container body">
        <div class="main_container">
            <?php include 'sidebar.php'; ?>
            <!-- page content -->
            <div class="right_col" role="main">
<h1>Badges</h1>
<form   class="badge-form">
<input type="hidden"  name="update_badge" required value='1'/>

  <section class="badge-field-container">
 <div>
 <label for="language">languages</label>
    <select name="language" class="" id="language">
      <option selected>select language</option>
      <option>2</option>
      <option>3</option>
      <option>4</option>
      <option>5</option>
    </select>
 </div>
  </section>


  <section class="badge-field-container badge-input-full">
 <aside> 
<h5>Notification Setting</h5>

<div >
 <label for="title">title</label>
<input name="title" type="text" id="title" class="">
</div>
</aside>
    <div >
    <label for="body">body</label>
    <textarea name="body" class="" id="body" ></textarea>
  </div>
  </section>


  <section class="badge-field-container">
    <aside>
    <h5>Dashing Debut</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Combat Winner</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Clash Winner</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Most Wanted Winner</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Ultimate Winner</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Quiz Warrior</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Super sonic</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Flashback</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Brainiac</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Big Thing</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Elite</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Thirsty</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Power Elite</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Sharing is Caring</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>

  <section class="badge-field-container">
    <aside>
    <h5>Streak</h5>
    <div><label for="icon">icon</label>
    <input type="file">
    </div>
    </aside>
    <div><label for="label">label</label>
    <input type="text" id="label">
    </div>
    <div class="">
    <label for="note">note</label>
    <textarea class="" id="note" ></textarea>
  </div>
  <div>
    <label for="reward">reward (coins)</label>
    <input type="number" id="reward">
    </div>
    <div>
    <label for="counter">counter</label>
    <input type="number" id="counter">
    </div>
  </section>
 

  <button type="submit" >Submit</button>
</form>
               
        </div>
    </div>
    <!-- footer content -->
    <?php include 'footer.php'; ?>
    <!-- /footer content -->
    </div>

    <script>
$('.badge-form').on('submit',function(event){
    event.preventDefault()
    let formData = new FormData(this)
    // $.ajax({
    //             url:'db_operations.php',  // Replace with your server URL
    //             type: 'POST',
    //             data: formData,
    //             success: function(response) {
    //                 // Handle the response from the server
    //                 $('button').html('Saved');
    //             },
    //             error: function(xhr, status, error) {
    //                 // Handle errors
    //                 $('button').html('try again');
    //             }
    //         });
    console.log(formData)
})


    </script>
</html>