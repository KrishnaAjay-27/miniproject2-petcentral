import sys
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing import image
import numpy as np

# Load the pre-trained model
model = load_model('pet_breed_model.h5')

# Load the image file from PHP script
img_path = sys.argv[1]

# Preprocess the image
img = image.load_img(img_path, target_size=(224, 224))  # Assuming model expects 224x224 input
img_array = image.img_to_array(img)
img_array = np.expand_dims(img_array, axis=0)  # Add batch dimension
img_array /= 255.0  # Normalize

# Predict breed
predictions = model.predict(img_array)
predicted_breed = np.argmax(predictions)

# Assuming you have a list of breed names
breed_names = ['Labrador', 'German Shepherd', 'Poodle', 'Bulldog', 'Beagle']  # Replace with your breed names
print(breed_names[predicted_breed])
