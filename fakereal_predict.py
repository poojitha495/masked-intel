import os
import numpy as np
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing import image
from tkinter import Tk, filedialog

print(" Starting real/fake prediction script...", flush=True)
print("Current working directory:", os.getcwd(), flush=True)

model_path = "real_fake_classifier_model.keras"

if not os.path.exists(model_path):
    print(f" Model file not found: {model_path}", flush=True)
    exit()

model = load_model(model_path)
print("Model loaded successfully.", flush=True)

Tk().withdraw()  
img_path = filedialog.askopenfilename(
    title="Select an image for real/fake classification",
    filetypes=[("Image files", "*.jpg *.jpeg *.png")]
)

if not img_path:
    print(" No image selected.", flush=True)
    exit()

# Check if file exists
if not os.path.exists(img_path):
    print(f" Image file not found: {img_path}", flush=True)
    exit()

# Preprocess image
IMG_SIZE = (128, 128)
img = image.load_img(img_path, target_size=IMG_SIZE)
img_array = image.img_to_array(img) / 255.0
img_array = np.expand_dims(img_array, axis=0)

print(" Image loaded and preprocessed.", flush=True)

# Predict
prediction = model.predict(img_array)[0][0]
label = "Real" if prediction > 0.7 else "Fake"
print(f" Prediction for '{img_path}': {label} (Confidence: {prediction:.2f})", flush=True)
