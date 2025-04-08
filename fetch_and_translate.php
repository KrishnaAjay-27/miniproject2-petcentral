<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Check if video path is provided
    if (!isset($_GET['videoPath'])) {
        throw new Exception('Video path not provided');
    }

$videoPath = $_GET['videoPath'];
    
    // Check if video file exists
    if (!file_exists($videoPath)) {
        throw new Exception('Video file not found at: ' . $videoPath);
    }

    // Check if FFmpeg exists
    $ffmpegPath = __DIR__ . '/ffmpeg/ffmpeg.exe';
    if (!file_exists($ffmpegPath)) {
        throw new Exception('FFmpeg not found at: ' . $ffmpegPath);
    }

    // Create output directory if it doesn't exist
    $outputDir = __DIR__ . '/audio_output';
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    // Generate a unique filename for the output
    $outputFile = $outputDir . '/audio_' . time() . '.wav';

    // Extract audio using FFmpeg
    $command = sprintf(
        '"%s" -i "%s" -vn -acodec pcm_s16le -ar 16000 -ac 1 "%s"',
        $ffmpegPath,
        $videoPath,
        $outputFile
    );

    // Log the command for debugging
    error_log("FFmpeg command: " . $command);

    $output = [];
    $returnVar = 0;
    exec($command . " 2>&1", $output, $returnVar);

    // Log the output and return value
    error_log("FFmpeg output: " . print_r($output, true));
    error_log("FFmpeg return value: " . $returnVar);

    if ($returnVar !== 0) {
        throw new Exception('FFmpeg command failed. Output: ' . implode("\n", $output));
    }

    // Check if the output file was created
    if (!file_exists($outputFile)) {
        throw new Exception('Failed to create audio file');
    }

    // Return success response with the audio file path
    echo json_encode([
        'success' => true,
        'message' => 'Audio extracted successfully',
        'audio_file' => basename($outputFile)
    ]);

} catch (Exception $e) {
    error_log('Audio extraction error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function translateText($text, $targetLanguage) {
    $url = "https://libretranslate.com/translate";

    $data = [
        'q' => $text,
        'target' => $targetLanguage,
        'source' => 'en' // Change this to your source language if needed
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $responseData = json_decode($response, true);

    // Check for translation results
    if (isset($responseData['translatedText'])) {
        return $responseData['translatedText'];
    } else {
        return ''; // Return an empty string if no translation is found
    }
}
?>
