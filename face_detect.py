import cv2
import tkinter as tk
from tkinter import filedialog

root = tk.Tk()
root.withdraw()

image_path = filedialog.askopenfilename(
    title="Select an image for face detection",
    filetypes=[("Image files", "*.jpg *.jpeg *.png *.bmp")]
)

if not image_path:
    print("No image selected.")
    exit()

print(f" Selected file: {image_path}")

face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

img = cv2.imread(image_path)
if img is None:
    print("Failed to load image.")
    exit()


gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5)


if len(faces) > 0:
    print(f"Face(s) detected: {len(faces)}")
else:
    print("No face detected.")
