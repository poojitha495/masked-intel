import cv2
import numpy as np
from tensorflow.keras.models import load_model

# Path to your trained model
model_path = r"C:\Users\Mallipudi.Susi\Desktop\hackathon\models\facial_hair_best_model.h5"

# Path to the image you want to predict
image_path = r"C:\Users\Mallipudi.Susi\Desktop\hackathon\me.JPG" 

# Load the model
model = load_model(model_path)

# Labels
labels = ['beard', 'no_beard']  


# Load and preprocess the image
img = cv2.imread(image_path)
if img is None:
    print("Error: Unable to read the image. Please check the path.")
    exit()

img = cv2.imread(image_path)
if img is None:
    print("Error: Unable to read the image. Please check the path.")
    exit()

img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
img = cv2.resize(img, (128, 128))  # Update to match training input size
img = img / 255.0
img = np.expand_dims(img, axis=0)


# Predict
prediction = model.predict(img)
predicted_class = np.argmax(prediction[0])

# Output
print("Prediction:", labels[predicted_class])
