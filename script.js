let model;

// Load the MobileNet model
mobilenet.load().then(loadedModel => {
    model = loadedModel;
    console.log('MobileNet model loaded');
});

document.addEventListener('DOMContentLoaded', () => {
    const imageUpload = document.getElementById('imageUpload');
    const classifyButton = document.getElementById('classifyButton');
    const uploadedImage = document.getElementById('uploadedImage');
    const result = document.getElementById('result');

    classifyButton.addEventListener('click', async () => {
        if (!imageUpload.files[0]) {
            alert('Please upload an image first');
            return;
        }

        const img = await loadImage(imageUpload.files[0]);
        uploadedImage.src = img.src;
        uploadedImage.style.display = 'block';

        result.innerHTML = 'Classifying...';

        // Use MobileNet to classify the image
        const predictions = await model.classify(img);

        // Log predictions to the console for debugging
        console.log(predictions);

        // Filter predictions to include both dog and cat breeds
        const dogBreeds = predictions.filter(p => p.className.toLowerCase().includes('dog'));
        const catBreeds = predictions.filter(p => p.className.toLowerCase().includes('cat'));

        // Check for dog breeds
        if (dogBreeds.length > 0) {
            const topDogBreed = dogBreeds[0].className.split(',')[0]; // Get the first part of the breed name
            result.innerHTML = `Detected Dog Breed: ${topDogBreed}`;
        } 
        // Check for cat breeds
        else if (catBreeds.length > 0) {
            const topCatBreed = catBreeds[0].className.split(',')[0]; // Get the first part of the breed name
            result.innerHTML = `Detected Cat Breed: ${topCatBreed}`;
        } 
        // If no breeds are detected
        else {
            result.innerHTML = 'No recognizable cat or dog breed detected. Please upload an image of a cat or dog.';
        }
    });
});

function loadImage(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = e.target.result;
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}