

# # import cv2
# # import numpy as np
# # from tensorflow.keras.models import load_model
# # import os

# # # Load the trained model
# # model = load_model(r"C:\Users\Lasya Priya\Desktop\hackathon\hackathon\models\age_prediction_model.h5")

# # IMG_HEIGHT, IMG_WIDTH = 100, 100

# # def predict_age(image_path):
# #     # Verify the file exists
# #     assert os.path.exists(image_path), f"Image file not found: {image_path}"

# #     # Load and preprocess the image
# #     img = cv2.imread(image_path)
# #     if img is None:
# #         print("Image not found.")
# #         return

# #     img = cv2.resize(img, (IMG_WIDTH, IMG_HEIGHT))
# #     img = img / 255.0
# #     img = np.expand_dims(img, axis=0)

# #     # Predict age
# #     age = model.predict(img)[0][0]
# #     print(f"Predicted age: {age:.2f} years")

# #     # Optional: display the image
# #     cv2.imshow("Input", cv2.imread(image_path))
# #     cv2.waitKey(0)
# #     cv2.destroyAllWindows()

# # # Usage example
# # predict_age(r"C:\Users\Lasya Priya\Desktop\WhatsApp Image 2025-05-31 at 01.51.13_1dd9aa78.jpg")  # Replace with your image path

# import cv2
# import numpy as np
# from tensorflow.keras.models import load_model

# # Load the trained model
# model = load_model(r"C:\Users\Lasya Priya\Desktop\hackathon\hackathon\models\age_prediction_model.h5")

# IMG_HEIGHT, IMG_WIDTH = 100, 100

# def predict_age_from_image(img):
#     """Preprocess and predict age from a given image."""
#     img = cv2.resize(img, (IMG_WIDTH, IMG_HEIGHT))
#     img = img / 255.0
#     img = np.expand_dims(img, axis=0)

#     age = model.predict(img)[0][0]
#     return age

# def capture_and_predict():
#     """Capture image from webcam and predict age."""
#     cap = cv2.VideoCapture(0)
#     if not cap.isOpened():
#         print("Error: Could not open webcam.")
#         return

#     print("Press 'SPACE' to capture the image and predict age, or 'ESC' to exit.")
#     while True:
#         ret, frame = cap.read()
#         if not ret:
#             print("Failed to grab frame.")
#             break

#         cv2.imshow("Webcam - Press SPACE to Capture", frame)

#         key = cv2.waitKey(1)
#         if key % 256 == 27:  # ESC pressed
#             print("Escape hit, closing...")
#             break
#         elif key % 256 == 32:  # SPACE pressed
#             age = predict_age_from_image(frame)
#             print(f"Predicted Age: {age:.2f} years")

#             # Show the captured image with predicted age
#             cv2.putText(frame, f"Age: {age:.2f} yrs", (10, 30),
#                         cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 255, 0), 2)
#             cv2.imshow("Captured Image", frame)
#             cv2.waitKey(0)  # Wait until any key is pressed

#     cap.release()
#     cv2.destroyAllWindows()

# # Run it
# capture_and_predict()


import cv2
import numpy as np
from tensorflow.keras.models import load_model

# Load the trained model
model = load_model(r"C:\Users\Lasya Priya\Desktop\hackathon\hackathon\models\age_prediction_model.h5")

# Image size expected by the model
IMG_HEIGHT, IMG_WIDTH = 100, 100

def predict_age_from_image(img):
    """Preprocess and predict age from a given image, then subtract 10."""
    img = cv2.resize(img, (IMG_WIDTH, IMG_HEIGHT))
    img = img / 255.0
    img = np.expand_dims(img, axis=0)

    predicted_age = model.predict(img)[0][0]
    adjusted_age = predicted_age - 10  
    return max(adjusted_age, 0)  

def capture_and_predict():
    """Capture image from webcam and predict adjusted age."""
    cap = cv2.VideoCapture(0)
    if not cap.isOpened():
        print("Error: Could not open webcam.")
        return

    print("Press 'SPACE' to capture the image and predict age, or 'ESC' to exit.")
    while True:
        ret, frame = cap.read()
        if not ret:
            print("Failed to grab frame.")
            break

        cv2.imshow("Webcam - Press SPACE to Capture", frame)

        key = cv2.waitKey(1)
        if key % 256 == 27:  # ESC key
            print("Escape hit, closing...")
            break
        elif key % 256 == 32:  # SPACE key
            adjusted_age = predict_age_from_image(frame)
            print(f"Predicted Age (Adjusted): {adjusted_age:.2f} years")

            
            display_frame = frame.copy()
            cv2.putText(display_frame, f"Age: {adjusted_age:.2f} yrs", (10, 30),
                        cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 255, 0), 2)
            cv2.imshow("Captured Image", display_frame)
            cv2.waitKey(0)  

    cap.release()
    cv2.destroyAllWindows()


capture_and_predict()
