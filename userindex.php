<?php include("header.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Pet Shop</title>
   <link rel="stylesheet" type="text/css" href="style1.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@200..900&display=swap">
   <style>
        /* Reset styles */
        * {
           margin: 0;
           padding: 0;
           box-sizing: border-box;
        }

        /* Image Section styles */
        .image-section {
           position: relative;
           width: 100vw;
           height: 80vh;
           overflow: hidden;
           display: flex;
           justify-content: center;
           align-items: center;
           background: url('res.jpg') no-repeat center center/cover;
        }

        .quote {
           position: absolute;
           text-align: center;
           color: white;
           text-shadow: 2px 2px 4px #000;
           padding: 20px;
           max-width: 80%;
           font-family: 'Crimson Pro', sans-serif;
        }

        .quote h2 {
           font-size: 3rem;
        }

        .quote p {
           font-size: 1.5rem;
           font-weight: bold;
           margin-top: 10px;
        }

        /* Container for categories */
        .categories {
           display: flex;
           justify-content: space-around;
           flex-wrap: wrap;
           padding: 20px;
           background-color: #f9f9f9;
        }

        .category {
           width: 22%;
           margin: 10px;
           text-align: center;
           border-radius: 10px;
           overflow: hidden;
           position: relative;
        }

        .category img {
           width: 100%;
           height: 200px;
           object-fit: cover;
           border-bottom: 5px solid #f9c74f;
        }

        .category h3 {
           font-size: 1.5rem;
           margin: 10px 0;
        }

        .category p {
           font-size: 1rem;
           color: #555;
        }

        .shop-button {
           background-color: #f9c74f;
           color: #333;
           border: none;
           padding: 10px 20px;
           font-size: 1rem;
           cursor: pointer;
           border-radius: 5px;
           margin: 10px 0;
           transition: background-color 0.3s ease;
        }

        .shop-button:hover {
           background-color: #f9844a;
        }
   </style>
</head>
<body>
   <!-- Image and Quotes Section -->
   <div class="image-section">
      <div class="quote">
         <h2>Welcome to Our Pet Shop</h2>
         <p>Your one-stop destination for all things pet!</p>
      </div>
   </div>

   <!-- Categories Section -->
   <div class="categories">
      <div class="category">
         <img src="fooddog.jpg" alt="Pet Food">
         <h3>Premium Pet Food  For Dog and Cat</h3>
         <p>Healthy and nutritious meals for your pets</p>
         
      </div>
      <div class="category">
         <img src="acc.jpg" alt="Pet Accessories">
         <h3>Pet Accessories For DoG and cat</h3>
         <p>Find the best gear for your pets</p>
         
      </div>
      <div class="category">
         <img src="gromming.jpg" alt="Pet Grooming">
         <h3>Pet Grooming Essentials For Dog and Cat </h3>
         <p>Everything you need for pet grooming</p>
         
      </div>
      <div class="category">
         <img src="pets.jpg" alt="Pets">
         <h3>Pets</h3>
         <p>Cats And Dogs to Buy</p>
       
      </div>
   </div>

   <!--  -->
</body>
</html>
<?php include("footer.php"); ?>