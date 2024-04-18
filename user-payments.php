<?php

if (isset($_GET['user_id'])) {
 $user_id = $_GET['user_id'];
 $sql = "SELECT * FROM payment_history WHERE user_id = '$user_id' ORDER BY  created_at DESC";
 include ('library/crud.php');
 include ('library/functions.php');
 $db = new Database();
 $db->connect();
 $db->sql($sql);
 $results = $db->getResult();

 

}
?>


<!DOCTYPE html>
<html lang="en">

 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>Payment | history</title>
 </head>

 <body class='p-10'>
  <h1 class='text-xl'>Billings</h1>
  <div class='p-5 h-screen overflow-auto'>
   <?php   
  if(empty($results)) {
 $title = "Empty!";
$text = "No data has been saved.";
$icon = "info";

$js_code = "<script>
  Swal.fire({
    title: '$title',
    text: '$text',
    icon: '$icon'
  }).then(()=>{
    window.history.back();
   });
</script>";

echo $js_code;
   exit();
  }
  
  
  ?>
   <table class='w-full text-left '>
    <thead class=' uppercase'>
     <tr>
      <th class=' text-gray-500  p-2'>username</th>
      <th class=' text-gray-500 p-2'>email</th>
      <th class=' text-gray-500 p-2'>reference</th>
      <th class=' text-gray-500 p-2'>currency</th>
      <th class=' text-gray-500 p-2'>amount</th>
      <th class=' text-gray-500 p-2'>channel</th>

      <th class=' text-gray-500 p-2'>date</th>
      <th class=' text-gray-500 p-2'>status</th>
     </tr>
    </thead>
    <tbody>
     <?php foreach($results as $result): ?>

     <tr class='border-b'>
      <td class='px-2 py-4 '>
       <?=$result['username']?>
      </td>
      <td class='px-2 py-4'>
       <?=$result['email']?>
      </td>
      <td class='px-2 py-4'>
       <?=$result['reference']?></td>
      <td class='px-2 py-4'> <?=$result['currency']?></td>
      <td class='px-2 py-4'> <?=$result['amount']/100?></td>
      <td class='p-2 py-4'> <?=$result['channel']?></td>
      <td class='px-2 py-4'> <?=$result['created_at']?></td>
      <td class='px-2 py-4'>

       <button class=" py-1 px-4 <?=$result['status'] === 'success' ? 'text-[#32b265] bg-[#d9fee8]' : '' ?>">
        <?=$result['status']?>
       </button>
      </td>

     </tr>
     <?php endforeach; ?>
    </tbody>
   </table>
  </div>

 </body>

</html>