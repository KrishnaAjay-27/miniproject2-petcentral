<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    .alert {
    /* background: #ffdb9b; */
    padding: 20px 40px;
    min-width: 420px;
    position: absolute;
    right: 0;
    top: 10px;
    border-radius: 4px;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
    margin-top:50px;
  }
  
  .alert.showAlert {
    opacity: 1;
    pointer-events: auto;
  }
  
  .alert.show {
    animation: show_slide 1s ease forwards;
  }
  
  @keyframes show_slide {
    0% {
      transform: translateX(100%);
    }
    40% {
      transform: translateX(-10%);
    }
    80% {
      transform: translateX(0%);
    }
    100% {
      transform: translateX(-10px);
    }
  }
  
  .alert.hide {
    animation: hide_slide 1s ease forwards;
  }
  
  @keyframes hide_slide {
    0% {
      transform: translateX(-10px);
    }
    40% {
      transform: translateX(0%);
    }
    80% {
      transform: translateX(-10%);
    }
    100% {
      transform: translateX(100%);
    }
  }
  
  .alert .fa-exclamation-circle {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #ce8500;
    font-size: 30px;
  }
  
  .alert .msg {
    padding: 0 20px;
    font-size: 18px;
    color: black;
  }
  
  .alert .close-btn {
    position: absolute;
    right: 0px;
    top: 50%;
    transform: translateY(-50%);
    /* background: #ffd080; */
    padding: 20px 18px;
    cursor: pointer;
  }
  
  .alert .close-btn:hover {
    background: #ffc766;
  }
  
  .alert .close-btn .fa {
    color: black;
    font-size: 22px;
    line-height: 40px;
  }
  
  </style>
</head>
<?php
function showAlert1($type, $message, $icon, $color) {
   echo '<div class="alert '.$type.' hide" style="background:'.$color.'">
         <span class="fa '.$icon.'" style="background:'.$color.';color:black;font-size:15px;"></span>
         <span class="msg" >'.$message.'</span>
         <div class="close-btn" style="background:'.$color.'">
            <span class="fa fa-close"></span>
         </div>
      </div>';
}
function showAlert2($type, $message, $icon, $color) {
   echo '<div class="alert '.$type.' hide" style="background:'.$color.'">
         <span class="fa '.$icon.'" style="background:'.$color.';color:black;font-size:15px;"></span>
         <span class="msg" >'.$message.'</span>
         <div class="close-btn" style="background:'.$color.'">
            <span class="fa fa-close"></span>
         </div>
      </div>';
}
?>

<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
<script>
  $(document).ready(function(){
    $('.alert').addClass("show");
    $('.alert').removeClass("hide");
    $('.alert').addClass("showAlert");
    setTimeout(function(){
      $('.alert').removeClass("show");
      $('.alert').addClass("hide");
    },5000);

    $('.close-btn').click(function(){
      $('.alert').removeClass("show");
      $('.alert').addClass("hide");
    });
  });
</script>
</html>