import tensorflow as tf
import cv2
import numpy as np

model = tf.keras.models.load_model("models/mask_detector.h5")

def preprocess_image(img_path, target_size=(224, 224)):
    img = cv2.imread(img_path)
    img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    img = cv2.resize(img, target_size)
    img = img / 255.0
    img = np.expand_dims(img, axis=0)
    return img

def predict_mask(img_path):
    input_img = preprocess_image(img_path)
    prediction = model.predict(input_img)
    if prediction[0][0] > 0.5:
        return "Mask detected"
    else:
        return "No mask detected"

if __name__ == "__main__":
    test_image_path =r"C:\Users\Mallipudi.Susi\Desktop\hackathon\girl_face.jpg"

    result = predict_mask(test_image_path)
    print(result)
