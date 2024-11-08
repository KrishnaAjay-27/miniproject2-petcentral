-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2024 at 09:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `cid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cid`, `name`, `status`) VALUES
(12, 'Accessories', 0),
(13, 'Food', 0),
(14, 'Pets', 0),
(15, 'dry food', 0),
(16, 'Grooming Products', 0),
(17, 'Toys', 0);

-- --------------------------------------------------------

--
-- Table structure for table `chat_message`
--

CREATE TABLE `chat_message` (
  `chatid` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `did` int(11) NOT NULL,
  `breed_name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `vaccination_status` varchar(100) NOT NULL,
  `problem` varchar(100) NOT NULL,
  `reply` varchar(100) NOT NULL,
  `medicine` varchar(200) NOT NULL,
  `reply_status` varchar(100) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_message`
--

INSERT INTO `chat_message` (`chatid`, `lid`, `did`, `breed_name`, `age`, `vaccination_status`, `problem`, `reply`, `medicine`, `reply_status`, `created_at`, `updated_at`) VALUES
(1, 45, 47, 'labour', 2, 'completed', 'not eating', 'nfcjdxavnhjdbmvcmb', '', 'replied', '2024-10-18 08:36:59', '00:00:00'),
(3, 45, 47, 'labour', 3, 'completed', 'no taking food for past 2 days', 'Based on the symptoms you’ve shared (loss of appetite for two days), your pet could be experiencing ', 'Oral Rehydration:  Electral Powder / Pedialyte: Mix in water and offer small amounts regularly to prevent dehydration.', 'replied', '2024-10-18 10:29:28', '00:00:00'),
(4, 45, 47, 'Husky', 1, 'not completed', 'Always Tiered', '', '', 'pending', '2024-10-25 16:13:59', '00:00:00'),
(5, 45, 47, 'Husky', 1, 'not completed', 'Always Tiered and eating stones', '', '', 'pending', '2024-10-25 16:15:00', '00:00:00'),
(6, 45, 47, 'Husky', 1, 'not completed', 'Always Tiered and eating stones', '', '', 'pending', '2024-10-25 16:18:03', '00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `d_registration`
--

CREATE TABLE `d_registration` (
  `did` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `doctor_code` varchar(100) NOT NULL,
  `u_type` int(11) NOT NULL DEFAULT 3,
  `phone` varchar(10) NOT NULL,
  `address` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `Qualification` varchar(100) NOT NULL,
  `experience` int(11) NOT NULL,
  `image1` varchar(100) NOT NULL,
  `certificateimg2` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `d_registration`
--

INSERT INTO `d_registration` (`did`, `lid`, `name`, `email`, `doctor_code`, `u_type`, `phone`, `address`, `state`, `district`, `Qualification`, `experience`, `image1`, `certificateimg2`) VALUES
(1, 47, 'Dr Marget', 'intrude35@gmail.com', '1B282B9CC4', 3, '9778135376', 'Raj nivas ,naranganam po kozhencherry', 'Kerala', 'Ernakulam', 'complete a Bachelor of Veterinary Science and Animal Husbandry (B.V. Sc. & A.H.) degree', 2, 'doctor.jpg', 'doctorcer.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `log_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `petid` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `old_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `updated_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `lid` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `u_type` int(11) NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`lid`, `email`, `password`, `u_type`, `reset_token`, `status`) VALUES
(16, 'admin@gmail.com', 'Admin@02', 0, '', 0),
(31, 'mira.krish27@gmail.com', 'Mira@12345', 2, '', 0),
(32, 'ishikakrishna.25@gmail.com', 'Ishika@123', 2, '', 0),
(43, 'krishna.ajaya2@gmail.com', 'Krishna@27', 1, '', 0),
(45, 'petcentral68@gmail.com', 'Pet@1234', 1, '', 0),
(47, 'intrude35@gmail.com', 'Krishna@02', 3, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `not`
--

CREATE TABLE `not` (
  `nid` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `message` varchar(100) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `note`
--

CREATE TABLE `note` (
  `nnid` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `message` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `message` varchar(225) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `lid`, `message`, `created_at`, `is_read`) VALUES
(1, 45, 'Your payment of 8263 has been successfully completed for Order ID: 87.', '2024-09-05 09:55:03', 1),
(2, 45, 'Your payment of 8263 has been successfully completed for Order ID: 88.', '2024-09-05 10:13:10', 1),
(3, 45, 'Your payment of 578 has been successfully completed for Order ID: 92.', '2024-10-17 17:41:01', 1),
(4, 45, 'Your payment of 24789 has been successfully completed for Order ID: 97.', '2024-10-17 18:08:39', 1),
(5, 45, 'Your payment of 24789 has been successfully completed for Order ID: 98.', '2024-10-17 18:10:40', 1),
(6, 45, 'Your payment of 700 has been successfully completed for Order ID: 100.', '2024-10-17 18:26:35', 1),
(7, 45, 'Your payment of 400 has been successfully completed for Order ID: 101.', '2024-10-17 18:37:03', 1),
(8, 45, 'Your payment of 400 has been successfully completed for Order ID: 102.', '2024-10-17 18:43:53', 1),
(9, 45, 'Your payment of 400 has been successfully completed for Order ID: 103.', '2024-10-17 18:47:21', 1),
(10, 45, 'Your payment of 3600 has been successfully completed for Order ID: 106.', '2024-10-18 00:05:02', 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `petid` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(50) NOT NULL,
  `product_name` varchar(50) NOT NULL,
  `image` varchar(225) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `order_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` int(11) NOT NULL DEFAULT 0,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petvacc`
--

CREATE TABLE `petvacc` (
  `vid` int(11) NOT NULL,
  `petid` int(11) NOT NULL,
  `vaccname` varchar(200) NOT NULL,
  `image3` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petvacc`
--

INSERT INTO `petvacc` (`vid`, `petid`, `vaccname`, `image3`) VALUES
(1, 1, '	DHPPi L Booster 1 + Rabies', 'vacc.jpg'),
(2, 2, 'kfrjgj', 'acc.jpg'),
(3, 3, '4hhbj', 'avatar.jpg'),
(4, 4, '4hhbj', 'avatar.jpg'),
(5, 5, '	DHPPi L Booster 1 + Rabies', 'vacc.jpg'),
(6, 6, ' DHPP and rabies', 'medicated2.webp'),
(7, 7, ' DHPP and rabies', 'medicated2.webp'),
(8, 8, '	DHPPi L Booster 1 + Rabies', 'medicated2.webp'),
(9, 9, 'Rabies Vaccine,FVRCP Vaccine', 'dryshampoo2.webp');

-- --------------------------------------------------------

--
-- Table structure for table `productpet`
--

CREATE TABLE `productpet` (
  `petid` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `Age` varchar(50) NOT NULL,
  `Gender` varchar(50) NOT NULL,
  `image1` varchar(255) NOT NULL,
  `image2` varchar(255) NOT NULL,
  `video` varchar(285) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `color` varchar(50) NOT NULL,
  `weight` decimal(10,2) NOT NULL,
  `height` decimal(10,2) NOT NULL,
  `sid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `subid` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productpet`
--

INSERT INTO `productpet` (`petid`, `product_name`, `description`, `Age`, `Gender`, `image1`, `image2`, `video`, `price`, `quantity`, `color`, `weight`, `height`, `sid`, `cid`, `subid`, `status`) VALUES
(5, 'Labrador Retrievers', 'The Labrador Retriever is a beloved and versatile breed, known for its friendly and outgoing nature. These medium to large-sized dogs have a strong, muscular build and a short, dense coat that comes in black, yellow, or chocolate. Labradors are intelligent, easy to train, and excel in various roles, from family pets to service and therapy dogs. They are highly social, thriving in environments where they can be active and engaged with their human companions. While generally healthy, Labradors require regular exercise, a balanced diet, and routine grooming to manage their energetic nature and maintain their well-being. Their loyal and affectionate temperament makes them a cherished member of any household.', '5months', 'Male', 'labor1.jpg', 'labor2.jpg', 'video1.mp4', 15000.00, 2, '0', 18.00, 12.00, 15, 14, 12, 0),
(6, 'Rajapalayam dog', 'The Rajapalayam Hound, also known as the Polygar Hound or Indian Ghost Hound, is a southern Indian dog breed. The breed is named after Rajapalayam, a town in the Virudhunagar, Tamil Nadu.', '1 year', 'Male', 'rajapalayan.jpg', 'raj2.png', 'video1.mp4', 30000.00, 7, '0', 20.00, 25.00, 15, 14, 12, 0),
(8, 'Indian Spitz', 'The Indian Spitz is a spitz dog breed belonging to the utility group. The Indian Spitz was one of the most popular dogs in India in the 1980s and 1990s when India\\\'s import rules made it difficult to import dogs of other breeds', '7 months', 'Female', 'Indian_Spitz_Dog.jpg', '330px-Greater_indian_spitz.jpg', 'video1.mp4', 20000.00, 1, '0', 10.00, 40.00, 15, 14, 12, 0),
(9, 'Persian cat', 'Persian cats are medium-sized cats with a broad, short build, and a round face with a short muzzle. They have large eyes, small, rounded ears, and chubby cheeks. Their coats are long, soft, and luxurious, and come in many colors and patterns', '2month', 'Female', 'cat1.jpg', 'cat.jpg', 'video1.mp4', 10000.00, 2, '0', 3.00, 6.00, 15, 14, 13, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_dog`
--

CREATE TABLE `product_dog` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `species` varchar(50) NOT NULL,
  `image1` varchar(255) NOT NULL,
  `image2` varchar(255) NOT NULL,
  `sid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `subid` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_dog`
--

INSERT INTO `product_dog` (`product_id`, `name`, `description`, `brand`, `species`, `image1`, `image2`, `sid`, `cid`, `subid`, `status`) VALUES
(17, 'Applaws Wet Cat Food for Kittens - Chicken Breast', 'Description\\r\\nApplaws foods for cats are made using premium quality, human-grade meat proteins with limited extra ingredients to create a pure, natural, and nutritious complementary food for your cat. The limited number of ingredients ensures your cat gets all the nutrition they need, with no unnecessary additives. Applaws complementary foods are designed to be served alongside a complete and balanced dry food to offer variety and a nutritional boost. Complementary feeding also helps to boost water intake for better hydration which supports kidney and urinary tract health.\\r\\n\\r\\nKey Features:\\r\\n\\r\\nMade with natural ingredients \\r\\nFormulated for kittens to give them the best start in life \\r\\nConsists of 53% chicken\\r\\nNatural source of taurine – essential for the proper functioning of the heart and eye health\\r\\nMade with limited ingredients – from as little as 3 ingredients\\r\\nComplementary pet food – Feed with any dry food for a complete and balanced diet.', '', '', 'cat food.webp', 'cat food.webp', 15, 13, 9, 0),
(18, 'Farmina Dry Food - N&D Ancestral Grain Dog Chicken & Pumpkin Puppy Medium/Maxi', 'N&D ANCESTRAL GRAIN is from Italian culinary tradition and uses a few, simple and genuine ingredients in order to offer your pet a balanced, complete diet with low glycaemic index cereals. 90% of protein from animal origin, 0% artificial preservatives, 0% corn. Pomegranate has antioxidant properties and is a natural source of vitamins (vitC), potassium, and phosphorus. Together with spelled and oats, uses excellent animal protein sources, provided by select partners. Prevents obesity and diabetes thanks to the presence of Ancestral Cereals and ensures the modular release of energy during the day. Complete food for puppies during weaning up to two months of age and gestating or breastfeeding bitches.\\r\\n\\r\\nKey Features:\\r\\n\\r\\nHigh Animal Protein Content - As a primary source of energy in dogs and cats is derived from Protein, N&D products are formulated with a very high content of protein from animal origin, which also improves the digestibility of the food.\\r\\nNo Artificial Preservat', '', '', 'dryfooddog.webp', 'dryfooddog1.webp', 15, 13, 9, 0),
(19, ' Click to expand   M-Pets Cat Collar - Zany Eco Collar (Multi Coloured Fishes)', 'Are you looking for a stylish collar for your new feline friend? Then, this M-Pets Cat Collar - Zany Eco Collar just might be it! This adorable collar comes in more than one color option and allows your cat to safely explore the outdoors with the safety release clip and bell. Plus, with style and strength, this collar is crafted to ensure long-lasting comfort for your furry pal. The one-size-fits-all collar can be adjusted to best suit your furry friend’s neck.\\r\\n\\r\\nBenefits:\\r\\n\\r\\nMade from 100% Recycled plastic bottles\\r\\nsafety release clip\\r\\nOne size\\r\\nWith bell\\r\\n\\r\\n\\r\\nWhether it is to strut in style or provide a sense of identity, custom cat collars ensure your cat\\\'s safety, identity, and sophistication! Cat collars are especially helpful to cat parents who have cats that enjoy exercise, spending time outdoors and or are always looking for new ways to escape. Get the best cat collars online at the best prices and quality only at Petsy.', '', '', 'catcollar.webp', 'catcollar.webp', 15, 12, 10, 0),
(22, 'DOG HARNESS (RADIUM STRIPE) (NYLON)', 'DOG HARNESS (RADIUM STRIPE) (NYLON)', '', '', 'dogacc1.png', 'dog acc.png', 15, 12, 14, 0),
(23, 'DOG MASSAGER', 'Effleurage involves long strokes along the body, which help to relax the muscles and promote blood flow. Petrissage involves kneading the muscles to relieve tension and enhance relaxation. Circular friction involves small, circular motions with your fingers or hands.', '', '', 'MASSAGER-L.jpg', 'MASSAGER-L-1-300x300.jpg', 15, 12, 15, 0),
(24, 'GINGER BERRY DOG SHAMPOO', 'Purifying action. Contains special substances, such as the important reinvigorating complex OCTOPIROX. Provides an antibacterial action and reduces itching on skin. Its special cosmetic/treating ingredients leave the entire coat looking fluffy, shiny and silky.', '', '', 'HHP_4514.jpg', 'HHP_4514 (1).jpg', 15, 16, 17, 0),
(25, 'Cat Collar - Red & White Checks', 'Cat Collar - Red & White Checks is a stylish and comfortable accessory designed to keep your cat both fashionable and safe. Featuring a classic red and white checkered pattern, this collar is perfect for cats of all sizes, offering a trendy look while providing essential functionality.\\r\\n\\r\\nKey Features:\\r\\nClassic Design: The red and white checkered pattern adds a timeless touch to your cat\\\'s appearance, suitable for any occasion.\\r\\nDurable Material: Made from high-quality, durable fabric that withstands daily wear and tear while remaining soft against your cat’s fur.\\r\\nAdjustable Fit: Designed with an adjustable buckle, ensuring a comfortable and secure fit for cats of various sizes.\\r\\nSafety Breakaway Buckle: Equipped with a breakaway safety feature that allows the collar to snap open if it gets caught, ensuring your cat\\\'s safety while exploring.\\r\\nAttached Bell: Comes with a small bell that helps you keep track of your cat’s movements and adds an adorable touch to the colla', '', '', 'Red_WhiteChecksCatCollar_1_compact.avif', 'Red_WhiteChecksCatCollar_1_compact.avif', 15, 12, 14, 0),
(26, 'Trixie Cat Shampoo for Long Hair', 'Trixie Cat Shampoo for Long Hair is specifically formulated to meet the grooming needs of cats with long fur. This gentle shampoo helps to keep your cat\\\'s coat clean, shiny, and tangle-free, ensuring a soft and luxurious feel after every wash.\\r\\n\\r\\nKey Features:\\r\\nTailored for Long-Haired Cats: The formula is specially designed for cats with long hair, helping to manage tangles and matting, leaving the coat smooth and manageable.\\r\\nGentle & Mild Formula: This shampoo is gentle on your cat’s skin, making it ideal for regular use without causing dryness or irritation.\\r\\nEnhances Shine & Softness: Enriched with ingredients that nourish your cat’s coat, it leaves the fur looking shiny and feeling incredibly soft to the touch.\\r\\nReduces Shedding & Matting: Helps to reduce shedding and prevent knots or mats from forming, making grooming easier and less stressful for both you and your cat.\\r\\nPleasant Fragrance: Leaves your cat smelling fresh and clean without overpowering their natura', '', '', 'TrixieCatShampooforLongHair250ml.jpg', '29191_3_1_compact.avif', 15, 16, 17, 0),
(27, 'puppee WIPE ME Pet Wipes 96 Pulls Sanitising Skin Grooming Anti-Bacterial Wet Wipes Pet Ear Eye Wipe', 'Infused with natural Lavender & DM water which helps in moisturising and healing properties on suts and dryness of paws, Great for dogs with sensitive skin and dogs with dry skin, these Puppee Wipe Me for Dogs are mild yet effective at cleaning away dirt, ALOE WITH VITAMIN E MOISTURIZERS - Vitamin E for calm,soothing skin & restore the area, leaves your pet happy and healthy, The Wipe Me for Pets 100 count canister is easy to use and closes to lock in moisture and freshness. After you\\\'re done using the wipes, simply close the top to keep wipes moist\\r\\n', '', '', 'wipe.jpg', 'wipes.webp', 15, 16, 19, 0),
(28, 'chullbull pet products 3 Cat Collar Belt WIth Bell Printed Nylon Cat , Puppy and rabbit Collar Charm', 'Pet Supplies are here with Set of 2 Collar. BRIGHT COLORS: 6 different bright colors available for cat id collars, looks great on all cats. ADJUSTABLE SIZE: The neck size is adjustable from 22 to 36 cm with breakaway buckle, 1 cm width. HIGH QUALITY: Made of nylon, the collars were built to last, durable and washable. BELL: Each kitten collar with bell has a bright colored and ring loud bell. WORKS ON MANY PETS: Great collars for cats, puppies and other small pets as well.', '', '', 'catcollar.jpg', 'catcollar2.jpg', 15, 12, 10, 0),
(29, 'PetsKart Luxurious Soft Velvet Sofa Shape Dog,Cat Pet Bed,Comfortable,With Pillow L Pet Bed  (Beige)', 'Petskart dog bed give a fun life to our furry friends who play a special role in our life. For this reason, we focused on providing safe and comfortable Pet beds.This dog bed is suitable for pets of large, medium and small Breeds.Friendly design can help the dog improve sleep quality and give the dog joint support.Rugged and Anti-skid base, Very soft & Cosy it is a pet bed worth experiencing.Non-slip points on the bottom reduces sliding on floors and prevents accidental penetration by water.\\r\\n\\r\\nHighlights:\\r\\nPet Type: Dog\\r\\nBed Type: Enclosed\\r\\nMaterial: Export Quality fabric ,Soft Cusion, Fiber\\r\\nWashable\\r\\nW x H: 81 cm x 74 cm (2 ft 7 in x 2 ft 5 in)', '', '', 'beddog.jpg', 'bed2.jpg', 15, 12, 21, 0),
(30, 'Active (Buy 1 Get 1 Free) Adult Chicken and Vegetables Vegetable 2.4 kg (2x1.2 kg) Dry Adult Dog Foo', 'Active Adult Dry Dog Food is a complete and balanced nutrient-rich food, which enhances the physical performance of your pet. It is enriched with essential ingredients along with vitamins and minerals keeping your pet active and agile. Our each ingredient undergoes a thorough selection process in which quality, safety and nutritional values are checked.', '', '', 'dogfooddd.webp', 'fooddogfgo.jpg', 15, 13, 9, 0),
(31, 'Buraq Astronaut Transparent Pet Carrier Backpack - for Travel, Hiking, Designed with Breathable Spac', 'Space Capsule Appearance - The Unique and Popular Space Capsule Design makes your cat carrier feel safer with an extensive view of their surroundings, enabling them to feel at Ease and Less Confined while Traveling.\\r\\nVentilation & Visibility - Extra 9 vent Holes around this Carrier Backpack and Two Side Windows to Ensure Excellent Airflow , Good Circulation and Visibility for your pet\\r\\nDurable & Spacious - 16.5 \\\" H x 10\\\" W x 12.5\\\" L. Suggested Weight: 0-7 kg for small to medium cats, 0-10lbs for Dogs,puppies. Most Airline Approved under seat. Important: Please Check your airline requirements before traveling.\\r\\nComfortable Strapes - The Wide, Padded, and Adjustable Shoulder Straps ensure your Comfort while Carrying your Pet. The Ergonomic Back design Evenly distributes weight for longer travel comfort while Carrying your Pet. The Ergonomic Back design Evenly distributes weight for longer travel comfort\\r\\nMade In India - Premium materials has been used The pet backpack is Super', '', '', 'travel pag.jpg', 'travelbag2.jpg', 15, 12, 22, 0),
(32, 'Drools Adult Dry Cat Food, Ocean Fish', 'About this item\\r\\nCrunchy chunks packed with real mackerel and sardine;Provides complete nutrition for cats\\r\\nEnriched with essential nutrients\\r\\nProvides better vision and shiny coat\\r\\nNo arti?cial ?avours or preservatives; Controls urinary PH\\r\\nTarget Audience Keywords: House-Cats; Container Type: Bag', '', '', 'drools.jpg', 'drools2.jpg', 15, 13, 9, 0),
(33, 'FRISKIES Seafood Sensations Adult Cat Dry Food, Tuna Salmon Whitefish Crab & Shrimp Flavours', 'About this item\\r\\nAvailable in blend of Mackerel, Tuna, Salmon, and Sardine flavour\\r\\nProtein - Protein To Help Maintain Strong Lean Muscles. Vitamin A & Taurine - Vitamin A & Taurine To Help Support Clear, Healthy Vision\\r\\nFatty Acids - Essential Fatty Acids With Omega 6 Fatty Acids For Healthy Coat. Antioxidants - Antioxidants To Help Support Natural Defenses\\r\\nColours And Preservatives - No Added Artificial Colours And Preservatives; Calcium - Calcium For Strong Teeth & Bones\\r\\nTarget Audience Keywords: House-Cats; Container Type: Bag', '', '', 'sea.webp', 'sea2.jpg', 15, 13, 9, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image1` varchar(255) NOT NULL,
  `image2` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `size`, `quantity`, `price`, `image1`, `image2`, `status`) VALUES
(26, 17, '70g x 12 Cans', 6, 1800.00, '', '', 0),
(27, 18, '12kg', 1, 8263.00, '', '', 0),
(28, 18, '10kg', 1, 6250.00, '', '', 0),
(29, 19, ' Fits neck size from about 22cm up to 29cm (approx 11 inches) Collar width is 1cm', 5, 276.00, '', '', 0),
(30, 22, 'A4P 1114-25-2', 0, 350.00, '', '', 0),
(31, 23, 'Large L', 10, 170.00, '', '', 0),
(32, 23, 'medium', 10, 150.00, '', '', 0),
(33, 24, '200ml', 2, 245.00, '', '', 0),
(34, 25, 'small', 1, 238.00, '', '', 0),
(35, 26, '250', 0, 350.00, '', '', 0),
(36, 27, '2pack', 8, 225.00, '', '', 0),
(37, 28, '3 packs', 4, 259.00, '', '', 0),
(38, 29, 'Large L', 3, 703.00, '', '', 0),
(39, 30, '2.4kg', 4, 319.00, '', '', 0),
(40, 31, ' 16.5 \" H x 10\" W x 12.5\" L', 5, 1329.00, '', '', 0),
(41, 32, '1.2kg', 5, 325.00, '', '', 0),
(42, 33, '1000.0 gram', 5, 356.00, '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

CREATE TABLE `registration` (
  `rid` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `landmark` varchar(100) NOT NULL,
  `pincode` int(11) NOT NULL,
  `roadname` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `state` varchar(50) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_code` varchar(100) NOT NULL,
  `u_type` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration`
--

INSERT INTO `registration` (`rid`, `lid`, `name`, `email`, `phone`, `address`, `landmark`, `pincode`, `roadname`, `district`, `state`, `verified`, `verification_code`, `u_type`) VALUES
(25, 43, 'Krishna Ajay', 'krishna.ajaya2@gmail.com', '2147483647', 'raj', 'Mtlp School Naranganam', 689642, 'Kadamanitta Road', 'Thiruvananthapuram', '', 1, '5a8c783e43630e85467e861040524951', 1),
(27, 45, '      Krishna Ajay', 'petcentral68@gmail.com', '9778135376', 'pet', 'mtlp', 689642, 'kannur', 'Kasaragod', 'Kerala', 1, 'a655af74f98f8748af938077e2e9e1c8', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subcategory`
--

CREATE TABLE `subcategory` (
  `subid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategory`
--

INSERT INTO `subcategory` (`subid`, `cid`, `name`, `status`) VALUES
(8, 13, 'wet food', 0),
(9, 13, 'dry food', 0),
(10, 12, 'collar belt', 0),
(11, 12, 'chain', 0),
(12, 14, 'Dog', 0),
(13, 14, 'Cat', 0),
(14, 12, 'collar', 0),
(15, 12, 'massager', 0),
(16, 12, 'Nail clippers', 0),
(17, 16, 'shampoos', 0),
(18, 16, 'conditioners', 0),
(19, 16, 'Body Care', 0),
(21, 12, 'bed care', 0),
(22, 12, 'Travel Bag', 0);

-- --------------------------------------------------------

--
-- Table structure for table `s_registration`
--

CREATE TABLE `s_registration` (
  `sid` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `address` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `u_type` int(11) NOT NULL,
  `supplier_code` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `s_registration`
--

INSERT INTO `s_registration` (`sid`, `lid`, `name`, `email`, `phone`, `address`, `state`, `district`, `u_type`, `supplier_code`) VALUES
(15, 31, 'Mira', 'mira.krish27@gmail.com', '9778135376', 'Raj nivas ,naranganam po kozhencherry', 'Kerala', 'Thrissur', 2, 'F5BD03E53B'),
(16, 32, 'ishika', 'ishikakrishna.25@gmail.com', '2147483647', 'daliya villa', 'Kerala', 'Kottayam', 2, 'B351D5388F');

-- --------------------------------------------------------

--
-- Table structure for table `tblpro`
--

CREATE TABLE `tblpro` (
  `tblid` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `brand` varchar(200) NOT NULL,
  `species` varchar(200) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblpro`
--

INSERT INTO `tblpro` (`tblid`, `product_id`, `brand`, `species`, `status`) VALUES
(6, 17, 'Applaws', 'cat food', 0),
(7, 18, 'Farmina', 'dog food', 0),
(8, 19, 'MPets', 'cat Accessories', 0),
(9, 20, '', '', 0),
(10, 21, '', '', 0),
(11, 22, 'All4Pets', 'dog Accessories', 0),
(12, 23, 'All4Pets', 'dog Accessories', 0),
(13, 24, 'All4Pets', 'dog grooming', 0),
(14, 25, 'petsale', 'cat Accessories', 0),
(15, 26, 'Trixie', 'cat grooming', 0),
(16, 27, 'puppee', 'dog grooming', 0),
(17, 28, 'Chullbull', 'cat Accessories', 0),
(18, 29, 'PetsKart', 'dog Accessories', 0),
(19, 30, 'Active', 'dog food', 0),
(20, 31, 'BURAQ', 'dog Accessories', 0),
(21, 32, 'Drools', 'cat food', 0),
(22, 33, 'FRISKIES', 'cat food', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cart`
--

CREATE TABLE `tbl_cart` (
  `cart_id` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `petid` int(11) NOT NULL,
  `size` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `date` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cart`
--

INSERT INTO `tbl_cart` (`cart_id`, `lid`, `product_id`, `petid`, `size`, `quantity`, `price`, `date`) VALUES
(52, 45, 19, 0, '29', 1, 276, '2024-10-25 16:57:12.216431'),
(55, 45, 30, 0, '39', 1, 319, '2024-10-25 18:33:58.682031');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_order`
--

CREATE TABLE `tbl_order` (
  `order_id` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_order`
--

INSERT INTO `tbl_order` (`order_id`, `lid`, `total`, `date`) VALUES
(71, 45, 23263, '2024-09-04'),
(72, 45, 8263, '2024-09-04'),
(73, 45, 8263, '2024-09-04'),
(74, 45, 1800, '2024-09-04'),
(75, 45, 1800, '2024-09-04'),
(76, 45, 1800, '2024-09-05'),
(77, 45, 1800, '2024-09-05'),
(78, 45, 1800, '2024-09-05'),
(79, 45, 6250, '2024-09-05'),
(80, 45, 6250, '2024-09-05'),
(81, 45, 6250, '2024-09-05'),
(82, 45, 6250, '2024-09-05'),
(83, 45, 8263, '2024-09-05'),
(84, 45, 8263, '2024-09-05'),
(85, 45, 8263, '2024-09-05'),
(86, 45, 8263, '2024-09-05'),
(87, 45, 8263, '2024-09-05'),
(88, 45, 8263, '2024-09-05'),
(89, 45, 200, '2024-09-23'),
(90, 45, 200, '2024-09-23'),
(91, 45, 438, '2024-10-17'),
(92, 45, 578, '2024-10-17'),
(93, 45, 390, '2024-10-17'),
(94, 45, 390, '2024-10-17'),
(95, 45, 510, '2024-10-17'),
(96, 45, 33052, '2024-10-17'),
(97, 45, 24789, '2024-10-17'),
(98, 45, 24789, '2024-10-17'),
(99, 45, 700, '2024-10-17'),
(100, 45, 700, '2024-10-17'),
(101, 45, 400, '2024-10-18'),
(102, 45, 400, '2024-10-18'),
(103, 45, 400, '2024-10-18'),
(104, 45, 1800, '2024-10-18'),
(105, 45, 5400, '2024-10-18'),
(106, 45, 3600, '2024-10-18'),
(107, 45, 3600, '2024-10-18'),
(108, 45, 3600, '2024-10-18'),
(109, 45, 3600, '2024-10-18'),
(110, 45, 3600, '2024-10-18'),
(111, 45, 3600, '2024-10-18'),
(112, 45, 3600, '2024-10-18'),
(113, 45, 3600, '2024-10-18'),
(114, 45, 3600, '2024-10-25'),
(115, 45, 326, '2024-10-26'),
(116, 45, 595, '2024-10-26'),
(117, 45, 595, '2024-10-26'),
(118, 45, 595, '2024-10-26'),
(119, 45, 595, '2024-10-26'),
(120, 45, 595, '2024-10-26'),
(121, 45, 595, '2024-10-26'),
(122, 45, 595, '2024-10-26'),
(123, 45, 595, '2024-10-26'),
(124, 45, 595, '2024-10-26');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product`
--

CREATE TABLE `tbl_product` (
  `pid` int(11) NOT NULL,
  `subid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `des` varchar(100) NOT NULL,
  `species` varchar(50) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `image` varchar(255) NOT NULL,
  `image2` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_save_later`
--

CREATE TABLE `tbl_save_later` (
  `save_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `petid` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `PostedDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_save_later`
--

INSERT INTO `tbl_save_later` (`save_id`, `cart_id`, `product_id`, `petid`, `lid`, `price`, `quantity`, `type`, `PostedDate`) VALUES
(1, 49, 9, 0, 45, 10000.00, 1, 'pet', '2024-10-25 23:01:58'),
(2, 47, 17, 0, 45, 1800.00, 1, 'dog', '2024-10-25 23:03:20'),
(3, 50, 31, 0, 45, 1329.00, 1, 'dog', '2024-10-25 23:03:41'),
(4, 51, 30, 0, 45, 319.00, 1, 'dog', '2024-10-25 23:05:20'),
(5, 53, 6, 0, 45, 30000.00, 1, 'pet', '2024-10-25 23:10:05'),
(6, 54, 0, 6, 45, 30000.00, 1, 'pet', '2024-10-25 23:10:39');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_weight`
--

CREATE TABLE `tbl_weight` (
  `weight_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_wishlist`
--

CREATE TABLE `tbl_wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `petid` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `posteddate` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_wishlist`
--

INSERT INTO `tbl_wishlist` (`wishlist_id`, `product_id`, `petid`, `lid`, `price`, `posteddate`) VALUES
(12, 11, 0, 45, 1700, '2024-08-12 10:24:56.000000'),
(17, 4, 0, 45, 250, '2024-08-12 10:24:49.000000'),
(18, 5, 0, 45, 250, '2024-08-12 10:24:53.000000'),
(19, 12, 0, 45, 0, '2024-08-12 15:25:27.000000'),
(23, 17, 0, 45, 1800, '2024-08-13 05:32:14.000000'),
(24, 19, 0, 45, 276, '2024-09-01 10:14:00.000000'),
(28, 0, 5, 45, 15000, '2024-09-01 11:44:13.000000'),
(29, 0, 9, 45, 10000, '2024-10-25 08:51:53.000000'),
(30, 25, 0, 45, 238, '2024-10-25 16:57:20.000000');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cid`);

--
-- Indexes for table `chat_message`
--
ALTER TABLE `chat_message`
  ADD PRIMARY KEY (`chatid`);

--
-- Indexes for table `d_registration`
--
ALTER TABLE `d_registration`
  ADD PRIMARY KEY (`did`);

--
-- Indexes for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`lid`);

--
-- Indexes for table `not`
--
ALTER TABLE `not`
  ADD PRIMARY KEY (`nid`);

--
-- Indexes for table `note`
--
ALTER TABLE `note`
  ADD PRIMARY KEY (`nnid`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`detail_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `petvacc`
--
ALTER TABLE `petvacc`
  ADD PRIMARY KEY (`vid`);

--
-- Indexes for table `productpet`
--
ALTER TABLE `productpet`
  ADD PRIMARY KEY (`petid`);

--
-- Indexes for table `product_dog`
--
ALTER TABLE `product_dog`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`);

--
-- Indexes for table `registration`
--
ALTER TABLE `registration`
  ADD PRIMARY KEY (`rid`);

--
-- Indexes for table `subcategory`
--
ALTER TABLE `subcategory`
  ADD PRIMARY KEY (`subid`);

--
-- Indexes for table `s_registration`
--
ALTER TABLE `s_registration`
  ADD PRIMARY KEY (`sid`);

--
-- Indexes for table `tblpro`
--
ALTER TABLE `tblpro`
  ADD PRIMARY KEY (`tblid`);

--
-- Indexes for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `tbl_order`
--
ALTER TABLE `tbl_order`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `tbl_product`
--
ALTER TABLE `tbl_product`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `tbl_save_later`
--
ALTER TABLE `tbl_save_later`
  ADD PRIMARY KEY (`save_id`);

--
-- Indexes for table `tbl_weight`
--
ALTER TABLE `tbl_weight`
  ADD PRIMARY KEY (`weight_id`);

--
-- Indexes for table `tbl_wishlist`
--
ALTER TABLE `tbl_wishlist`
  ADD PRIMARY KEY (`wishlist_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `chat_message`
--
ALTER TABLE `chat_message`
  MODIFY `chatid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `d_registration`
--
ALTER TABLE `d_registration`
  MODIFY `did` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `lid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `not`
--
ALTER TABLE `not`
  MODIFY `nid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `note`
--
ALTER TABLE `note`
  MODIFY `nnid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `petvacc`
--
ALTER TABLE `petvacc`
  MODIFY `vid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `productpet`
--
ALTER TABLE `productpet`
  MODIFY `petid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_dog`
--
ALTER TABLE `product_dog`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `registration`
--
ALTER TABLE `registration`
  MODIFY `rid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `subcategory`
--
ALTER TABLE `subcategory`
  MODIFY `subid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `s_registration`
--
ALTER TABLE `s_registration`
  MODIFY `sid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tblpro`
--
ALTER TABLE `tblpro`
  MODIFY `tblid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `tbl_order`
--
ALTER TABLE `tbl_order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `tbl_product`
--
ALTER TABLE `tbl_product`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_save_later`
--
ALTER TABLE `tbl_save_later`
  MODIFY `save_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_weight`
--
ALTER TABLE `tbl_weight`
  MODIFY `weight_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_wishlist`
--
ALTER TABLE `tbl_wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
