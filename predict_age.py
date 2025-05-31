import tensorflow as tf
import cv2
import numpy as np

# Load model with custom_objects to fix 'mse' not found error
model = tf.keras.models.load_model(
    'models/age_gender_model.h5',
    custom_objects={'mse': tf.keras.losses.MeanSquaredError}
)

# Update this path to your input image
image_path = r"C:\Users\Mallipudi.Susi\Desktop\hackathon\girl_face.jpg"


# Image size must match model input size
IMG_SIZE = 64

# Load and preprocess the image
img = cv2.imread(image_path)
if img is None:
    raise ValueError("Could not read input image, check the path.")
img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
img = cv2.resize(img, (IMG_SIZE, IMG_SIZE))
img = img / 255.0  # Normalize to [0,1]

# Expand dims to add batch size of 1
input_img = np.expand_dims(img, axis=0)

# Predict age (regression) and gender (classification)
age_pred, gender_pred = model.predict(input_img)

# Post-process predictions
predicted_age = age_pred[0][0] * 100  # Since age was normalized by 100
predicted_gender = np.argmax(gender_pred[0])  # 0 or 1

gender_label = "Male" if predicted_gender == 1 else "Female"

print(f"Predicted Age: {predicted_age:.1f}")
print(f"Predicted Gender: {gender_label}")
