import os
import numpy as np
import cv2
from tensorflow.keras.utils import to_categorical
from tensorflow.keras.models import Model
from tensorflow.keras.layers import Input, Conv2D, MaxPooling2D, Flatten, Dense, Dropout, BatchNormalization
from tensorflow.keras.optimizers import Adam
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from tensorflow.keras.callbacks import EarlyStopping, ModelCheckpoint
from sklearn.model_selection import train_test_split

dataset_path = r"C:\Users\Mallipudi.Susi\Desktop\hackathon\dataset\facial hair"
IMG_SIZE = 128

def load_facial_hair_data(dataset_dir, img_size=128):
    images = []
    labels = []
    for label_dir in ['no_beard', 'beard']:
        path = os.path.join(dataset_dir, label_dir)
        label = 0 if label_dir == 'no_beard' else 1
        for filename in os.listdir(path):
            img_path = os.path.join(path, filename)
            img = cv2.imread(img_path)
            if img is None:
                print(f"Warning: Could not read {img_path}, skipping.")
                continue
            img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
            img = cv2.resize(img, (img_size, img_size))
            img = img / 255.0
            images.append(img)
            labels.append(label)
    return np.array(images, dtype=np.float32), np.array(labels, dtype=np.int32)

print("Loading data...")
x, y = load_facial_hair_data(dataset_path, IMG_SIZE)
print(f"Loaded {len(x)} images.")

y_cat = to_categorical(y, num_classes=2)
x_train, x_val, y_train, y_val = train_test_split(
    x, y_cat, test_size=0.2, random_state=42, stratify=y)

# Augmentation setup
datagen = ImageDataGenerator(
    rotation_range=15,
    width_shift_range=0.1,
    height_shift_range=0.1,
    zoom_range=0.1,
    horizontal_flip=True,
    fill_mode='nearest'
)
datagen.fit(x_train)

# Model architecture with batch normalization
inputs = Input(shape=(IMG_SIZE, IMG_SIZE, 3))
x = Conv2D(32, (3,3), padding='same', activation='relu')(inputs)
x = BatchNormalization()(x)
x = MaxPooling2D()(x)

x = Conv2D(64, (3,3), padding='same', activation='relu')(x)
x = BatchNormalization()(x)
x = MaxPooling2D()(x)

x = Conv2D(128, (3,3), padding='same', activation='relu')(x)
x = BatchNormalization()(x)
x = MaxPooling2D()(x)

x = Flatten()(x)
x = Dense(128, activation='relu')(x)
x = Dropout(0.5)(x)
outputs = Dense(2, activation='softmax')(x)

model = Model(inputs=inputs, outputs=outputs)
model.compile(
    optimizer=Adam(learning_rate=1e-4),
    loss='categorical_crossentropy',
    metrics=['accuracy']
)

model.summary()

# Callbacks for early stopping and best model saving
callbacks = [
    EarlyStopping(monitor='val_loss', patience=5, restore_best_weights=True),
    ModelCheckpoint('models/facial_hair_best_model.h5', save_best_only=True)
]

# Train using augmented data generator
history = model.fit(
    datagen.flow(x_train, y_train, batch_size=64),
    validation_data=(x_val, y_val),
    epochs=30,
    callbacks=callbacks
)

print("Training complete. Best model saved as 'models/facial_hair_best_model.h5'")
