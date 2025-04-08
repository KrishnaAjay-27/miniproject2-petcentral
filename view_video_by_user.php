<?php

require('connection.php');
include("header.php");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Fetch video details from the doctor_pet_videos table
$query = "
    SELECT id, did, title, description, category, video_path, thumbnail_path,content_file, upload_date 
    FROM doctor_pet_videos 
    ORDER BY upload_date DESC
";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if the query returns any rows
if (mysqli_num_rows($result) > 0) {
    $videos = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $videos = []; // No videos found
    error_log("No videos found for the query: " . $query);
}
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Videos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #f9c74f;
            --secondary-color: #ffd166;
            --accent-color: #ffba08;
            --text-color: #333;
            --bg-color: #fff9eb;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
        }

        .main-content {
            margin-left: 260px;
            padding: 40px;
            min-height: 100vh;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 40px;
            position: relative;
        }

        .page-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 25px;
            position: relative;
            display: inline-block;
            padding: 0 0 10px 0;
            background: linear-gradient(to right, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-title::after {
            display: none;
        }

        /* Video Grid */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .video-card {
            background: white;
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .video-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(249, 199, 79, 0.2);
        }

        .video-thumbnail-wrapper {
            position: relative;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            overflow: hidden;
        }

        .video-thumbnail {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .video-card:hover .video-thumbnail {
            transform: scale(1.05);
        }

        .video-info {
            padding: 25px;
        }

        .video-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 12px;
            line-height: 1.4;
            color: var(--text-color);
        }

        .video-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .video-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meta-item i {
            color: var(--primary-color);
        }

        /* Buttons */
        .btn-watch {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: var(--text-color) !important;
        }

        .btn-watch:hover {
            background-color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(249, 199, 79, 0.3);
        }

        .btn-watch i {
            font-size: 1.2rem;
            color: var(--text-color);
        }

        /* Modal Enhancements */
        .modal-content {
            border-radius: 20px;
            overflow: hidden;
        }

        .modal-header {
            padding: 20px 25px;
            background: var(--primary-color);
        }

        .modal-title {
            font-weight: 600;
            color: var(--text-color);
        }

        .modal-body {
            padding: 25px;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-weight: 600;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .video-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h2 class="page-title">Educational Videos</h2>
                <p class="text-muted">Learn from our expert veterinarians</p>
            </div>

            <?php if (empty($videos)): ?>
                <div class="empty-state">
                    <i class="fas fa-video"></i>
                    <h3>No Videos Available</h3>
                    <p class="text-muted">Check back later for new educational content</p>
                </div>
            <?php else: ?>
                <div class="video-grid">
                    <?php foreach($videos as $video): ?>
                        <div class="video-card">
                            <div class="video-thumbnail-wrapper">
                                <?php if($video['thumbnail_path']): ?>
                                    <img src="<?php echo htmlspecialchars($video['thumbnail_path']); ?>" 
                                         class="video-thumbnail" alt="<?php echo htmlspecialchars($video['title']); ?>">
                                <?php else: ?>
                                    <div class="video-thumbnail bg-secondary d-flex align-items-center justify-content-center">
                                        <i class="fas fa-play-circle fa-3x text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="video-info">
                                <h3 class="video-title"><?php echo htmlspecialchars($video['title']); ?></h3>
                                <p class="video-description"><?php echo htmlspecialchars($video['description']); ?></p>
                                <div class="video-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-folder"></i>
                                        <?php echo htmlspecialchars($video['category']); ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo htmlspecialchars($video['upload_date']); ?>
                                    </div>
                                </div>
                                <button class="btn btn-watch" 
                                        onclick="openVideoModal('<?php echo htmlspecialchars($video['video_path']); ?>', <?php echo $video['id']; ?>)">
                                    <i class="fas fa-play"></i>
                                    Watch Video
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Video Modal -->
    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalLabel">Watch Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <video id="videoPlayer" class="w-100" controls>
                        Your browser does not support the video tag.
                    </video>
                    
                    <!-- File Actions -->
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-sm btn-success" id="downloadContentBtn">
                            <i class="fas fa-download"></i> Download Content
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="$('#fileUploadInput').click()">
                            <i class="fas fa-upload"></i> Upload Content
                        </button>
                        <input type="file" id="fileUploadInput" accept=".txt,.srt" style="display: none">
                    </div>

                    <!-- Transcript Area -->
                    <div class="mt-3">
                        <label for="transcriptArea" class="form-label">Content/Transcript</label>
                        <textarea id="transcriptArea" class="form-control" rows="4"></textarea>
                    </div>

                    <!-- Translation Options -->
                    <div class="mt-3">
                        <label for="targetLanguage" class="form-label">Target Language</label>
                        <select id="targetLanguage" class="form-select">
                            <option value="ml">Malayalam</option>
                            <option value="hi">Hindi</option>
                            <option value="ta">Tamil</option>
                            <option value="en">English</option>
                        </select>
                    </div>

                    <!-- Translated Content -->
                    <div class="mt-3">
                        <label for="translatedArea" class="form-label">Translation</label>
                        <textarea id="translatedArea" class="form-control" rows="4" readonly></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="translateContent()">Translate</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Include video transcription script -->
    <script src="video_handler.js"></script>
</body>
</html>