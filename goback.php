<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    /* Button Styling */
    .go-back-button {
      background-color: darkcyan; /* Bright yellow background */
      color: white; /* White text */
      padding: 12px 20px; /* Padding around the text */
      border: none; /* Remove default border */
      border-radius: 6px; /* Rounded corners */
      cursor: pointer; /* Pointer cursor on hover */
      font-size: 16px; /* Font size */
      display: flex; /* Use flexbox for alignment */
      align-items: center; /* Center items vertically */
      text-decoration: none; /* Remove underline */
      transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease; /* Smooth transitions */
      margin-top: 100px;
      margin-left: 30px;
    }

    .go-back-button i {
      margin-right: 8px; /* Space between icon and text */
    }

    .go-back-button:hover {
      background-color: darkcyan; /* Darker yellow on hover */
      transform: translateY(-2px); /* Lift button effect */
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Shadow effect */
    }

    .go-back-button:active {
      background-color: darkblue; /* Even darker yellow on click */
      transform: translateY(0); /* Remove lift effect */
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
    }
  </style>
</head>
<body>
  <button type="button" onclick="goBack()" class="go-back-button">
    <i class="fa fa-arrow-left"></i>
  </button>

  <script>
    function goBack() {
      window.history.back();
    }
  </script>
</body>
</html>
