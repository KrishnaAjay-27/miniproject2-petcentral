<?php
include('header.php');
require('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Fetch all videos uploaded by doctors along with their details
$query = "
    SELECT dpv.*, d.name AS doctor_name, d.qualification 
    FROM doctor_pet_videos dpv 
    JOIN doctors d ON dpv.did = d.did 
    ORDER BY dpv.upload_date DESC
";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$videos = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Pet Videos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 260px; /* Adjust based on your sidebar width */
            padding: 20px;
        }
        .video-card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .video-thumbnail {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .page-title {
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <h2 class="page-title">All Pet Videos</h2>
            <div class="row">
                <?php if (empty($videos)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            No videos uploaded yet. Please check back later!
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach($videos as $video): ?>
                        <div class="col-md-4">
                            <div class="card video-card">
                                <div class="position-relative">
                                    <?php if($video['thumbnail_path']): ?>
                                        <img src="<?php echo htmlspecialchars($video['thumbnail_path']); ?>" 
                                             class="video-thumbnail" alt="<?php echo htmlspecialchars($video['title']); ?>">
                                    <?php else: ?>
                                        <div class="video-thumbnail bg-secondary d-flex align-items-center justify-content-center">
                                            <i class="bi bi-play-circle fs-1 text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($video['title']); ?></h5>
                                    <p class="card-text text-muted">
                                        <?php echo htmlspecialchars($video['description']); ?>
                                    </p>
                                    <p class="card-text">
                                        <strong>Doctor:</strong> <?php echo htmlspecialchars($video['doctor_name']); ?><br>
                                        <strong>Qualification:</strong> <?php echo htmlspecialchars($video['qualification']); ?>
                                    </p>
                                    <div class="mt-3">
                                        <a href="watch_video.php?id=<?php echo $video['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm w-100">Watch Video</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
