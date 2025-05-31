import cv2
import numpy as np
from tensorflow.keras.models import load_model
from tkinter import Tk, filedialog


IMG_HEIGHT, IMG_WIDTH = 64, 64
LABELS = ['down', 'left', 'right', 'straight', 'up']


model = load_model('final_eye_gaze_model.keras')


cascade_path = cv2.data.haarcascades + 'haarcascade_lefteye_2splits.xml'
left_eye_cascade = cv2.CascadeClassifier(cascade_path)

def predict_eye_position(image_path):
    img = cv2.imread(image_path)
    if img is None:
        return None, "Could not read image"

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    eyes = left_eye_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5)

    if len(eyes) == 0:
        return None, "Left eye not detected"

    x, y, w, h = eyes[0]
    eye_img = img[y:y+h, x:x+w]
    eye_img = cv2.resize(eye_img, (IMG_WIDTH, IMG_HEIGHT))
    eye_img = cv2.cvtColor(eye_img, cv2.COLOR_BGR2RGB)
    eye_img = eye_img.astype('float32') / 255.0
    eye_img = np.expand_dims(eye_img, axis=0)

    predictions = model.predict(eye_img)[0]
    class_idx = np.argmax(predictions)
    label = LABELS[class_idx]
    confidence = int(predictions[class_idx] * 100)
    return label, confidence

if __name__ == '__main__':
    
    Tk().withdraw()  
    image_path = filedialog.askopenfilename(
        title="Select an image for gaze prediction",
        filetypes=[("Image files", "*.jpg *.jpeg *.png")]
    )

    if not image_path:
        print(" No image selected.")
    else:
        label, result = predict_eye_position(image_path)
        if label is None:
            print(f" {result}")
        else:
            print(f" Predicted Gaze Direction: {label}")
            print(f" Confidence: {result}%")
