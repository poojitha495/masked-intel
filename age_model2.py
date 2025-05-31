# import os
# import cv2
# import numpy as np
# from tensorflow.keras.models import Sequential
# from tensorflow.keras.layers import Conv2D, MaxPooling2D, Flatten, Dense, Dropout
# from tensorflow.keras.optimizers import Adam
# from sklearn.model_selection import train_test_split

# # Constants
# IMG_HEIGHT, IMG_WIDTH = 100, 100
# DATASET_DIR = r"C:\Users\Lasya Priya\Desktop\hackathon\hackathon\dataset\age and gender\UTKFace"  # Replace with your path

# def load_utkface_dataset(dataset_path):
#     images = []
#     ages = []

#     for filename in os.listdir(dataset_path):
#         if filename.endswith('.jpg'):
#             try:
#                 age = int(filename.split('_')[0])  # UTKFace filename: age_gender_race.jpg
#                 img_path = os.path.join(dataset_path, filename)
#                 img = cv2.imread(img_path)
#                 img = cv2.resize(img, (IMG_WIDTH, IMG_HEIGHT))
#                 images.append(img)
#                 ages.append(age)
#             except Exception as e:
#                 print(f"Skipping {filename}: {e}")

#     return np.array(images), np.array(ages)

# # Load data
# X, y = load_utkface_dataset(DATASET_DIR)
# X = X / 255.0

# # Split
# X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# # Define model
# model = Sequential([
#     Conv2D(32, (3, 3), activation='relu', input_shape=(IMG_HEIGHT, IMG_WIDTH, 3)),
#     MaxPooling2D(2, 2),

#     Conv2D(64, (3, 3), activation='relu'),
#     MaxPooling2D(2, 2),

#     Conv2D(128, (3, 3), activation='relu'),
#     MaxPooling2D(2, 2),

#     Flatten(),
#     Dense(128, activation='relu'),
#     Dropout(0.5),
#     Dense(1)  # Regression: single output value (age)
# ])

# model.compile(optimizer=Adam(learning_rate=0.001), loss='mean_squared_error', metrics=['mae'])

# # Train
# model.fit(X_train, y_train, epochs=10, batch_size=64, validation_data=(X_test, y_test))

# # Save model
# model.save("age_prediction_model.h5")


import os
import numpy as np
import cv2
from sklearn.model_selection import train_test_split
from tensorflow.keras.applications import MobileNetV2
from tensorflow.keras.models import Model
from tensorflow.keras.layers import Dense, GlobalAveragePooling2D, Input
from tensorflow.keras.optimizers import Adam
from tensorflow.keras.preprocessing.image import ImageDataGenerator

# Constants
IMG_HEIGHT, IMG_WIDTH = 224, 224
DATASET_DIR = r"C:\Users\Lasya Priya\Desktop\hackathon\hackathon\dataset\age and gender\UTKFace"  # Replace with your dataset path
MAX_AGE = 116  # Normalize target ages between 0 and 1

def load_utkface_dataset(dataset_path):
    images, ages = [], []
    for filename in os.listdir(dataset_path):
        if filename.endswith('.jpg'):
            try:
                age = int(filename.split('_')[0])
                img_path = os.path.join(dataset_path, filename)
                img = cv2.imread(img_path)
                img = cv2.resize(img, (IMG_WIDTH, IMG_HEIGHT))
                images.append(img)
                ages.append(age)
            except:
                continue
    return np.array(images), np.array(ages)

# Load and normalize dataset
X, y = load_utkface_dataset(DATASET_DIR)
X = X / 255.0
y = y / MAX_AGE  # Normalize target

# Split
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Image augmentation
datagen = ImageDataGenerator(horizontal_flip=True, zoom_range=0.1)

# Model: MobileNetV2 + regression head
base_model = MobileNetV2(input_shape=(IMG_HEIGHT, IMG_WIDTH, 3), include_top=False, weights='imagenet')
x = GlobalAveragePooling2D()(base_model.output)
x = Dense(128, activation='relu')(x)
x = Dense(1, activation='linear')(x)  # Regression output
model = Model(inputs=base_model.input, outputs=x)

# Freeze base_model layers for transfer learning
for layer in base_model.layers:
    layer.trainable = False

model.compile(optimizer=Adam(1e-4), loss='mse', metrics=['mae'])

# Train
model.fit(datagen.flow(X_train, y_train, batch_size=32), 
          epochs=10, validation_data=(X_test, y_test))

# Save model
model.save("age_predictor_mobilenetv2.h5")

# import os
# import numpy as np
# import cv2
# from tensorflow.keras.applications import MobileNetV2
# from tensorflow.keras.models import Model
# from tensorflow.keras.layers import Dense, GlobalAveragePooling2D
# from tensorflow.keras.optimizers import Adam
# from tensorflow.keras.utils import Sequence

# # Constants
# IMG_HEIGHT, IMG_WIDTH = 128, 128
# DATASET_DIR = r"C:\Users\Lasya Priya\Desktop\hackathon\hackathon\dataset\age and gender\UTKFace"
# MAX_AGE = 116
# BATCH_SIZE = 32

# # Custom data generator
# class AgeDataGenerator(Sequence):
#     def __init__(self, image_paths, labels, batch_size, img_height, img_width):
#         self.image_paths = image_paths
#         self.labels = labels
#         self.batch_size = batch_size
#         self.img_height = img_height
#         self.img_width = img_width

#     def __len__(self):
#         return int(np.ceil(len(self.image_paths) / self.batch_size))

#     def __getitem__(self, idx):
#         batch_paths = self.image_paths[idx * self.batch_size:(idx + 1) * self.batch_size]
#         batch_labels = self.labels[idx * self.batch_size:(idx + 1) * self.batch_size]

#         images = []
#         for path in batch_paths:
#             img = cv2.imread(path)
#             img = cv2.resize(img, (self.img_width, self.img_height))
#             img = img / 255.0
#             images.append(img)

#         return np.array(images), np.array(batch_labels)

# # Load dataset
# def load_utkface_dataset(dataset_path):
#     image_paths, ages = [], []
#     for filename in os.listdir(dataset_path):
#         if filename.endswith('.jpg'):
#             try:
#                 age = int(filename.split('_')[0])
#                 img_path = os.path.join(dataset_path, filename)
#                 image_paths.append(img_path)
#                 ages.append(age / MAX_AGE)  # Normalize age
#             except Exception as e:
#                 print(f"Skipping {filename}: {e}")
#     return image_paths, ages

# image_paths, ages = load_utkface_dataset(DATASET_DIR)

# # Split dataset
# from sklearn.model_selection import train_test_split
# train_paths, val_paths, train_labels, val_labels = train_test_split(image_paths, ages, test_size=0.2, random_state=42)

# # Create data generators
# train_generator = AgeDataGenerator(train_paths, train_labels, BATCH_SIZE, IMG_HEIGHT, IMG_WIDTH)
# val_generator = AgeDataGenerator(val_paths, val_labels, BATCH_SIZE, IMG_HEIGHT, IMG_WIDTH)

# # Model: MobileNetV2 + regression head
# base_model = MobileNetV2(input_shape=(IMG_HEIGHT, IMG_WIDTH, 3), include_top=False, weights='imagenet')
# x = GlobalAveragePooling2D()(base_model.output)
# x = Dense(128, activation='relu')(x)
# x = Dense(1, activation='linear')(x)  # Regression output
# model = Model(inputs=base_model.input, outputs=x)

# # Freeze base_model layers for transfer learning
# for layer in base_model.layers:
#     layer.trainable = False

# model.compile(optimizer=Adam(1e-4), loss='mse', metrics=['mae'])

# # Train
# model.fit(train_generator, epochs=10, validation_data=val_generator)

# # Save model
# model.save("age_predictor_mobilenetv2.h5")