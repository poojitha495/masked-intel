import os
import numpy as np
import cv2
from tensorflow.keras.utils import to_categorical
from tensorflow.keras.models import Model
from tensorflow.keras.layers import Input, Conv2D, MaxPooling2D, Flatten, Dense, Dropout
from tensorflow.keras.optimizers import Adam
from sklearn.model_selection import train_test_split

# Update this path to your UTKFace dataset folder (where the image files are)
dataset_path = r"C:\Users\Mallipudi.Susi\Desktop\hackathon\dataset\age and gender\UTKFace"

IMG_SIZE = 64  # Resize images to 64x64

def load_utkface_data(dataset_dir, img_size=64, max_samples=None):
    images = []
    ages = []
    genders = []
    
    files = os.listdir(dataset_dir)
    if max_samples:
        files = files[:max_samples]
    
    for file in files:
        try:
            # Filename format: age_gender_race_date.jpg.chip (e.g. 23_0_0_20170109150557335.jpg.chip)
            parts = file.split('_')
            age = int(parts[0])
            gender = int(parts[1])
            
            img_path = os.path.join(dataset_dir, file)
            img = cv2.imread(img_path)
            if img is None:
                print(f"Warning: Could not read image {file}, skipping.")
                continue
            img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
            img = cv2.resize(img, (img_size, img_size))
            img = img / 255.0  # Normalize to [0,1]
            
            images.append(img)
            ages.append(age)
            genders.append(gender)
        except Exception as e:
            print(f"Skipping {file}: {e}")
            continue
    
    images = np.array(images, dtype=np.float32)
    ages = np.array(ages, dtype=np.float32)
    genders = np.array(genders, dtype=np.int32)
    
    return images, ages, genders

# Load dataset (limit max_samples if needed)
x, age_labels, gender_labels = load_utkface_data(dataset_path, IMG_SIZE, max_samples=10000)

if len(x) == 0:
    raise ValueError("No images loaded. Check your dataset path and filenames.")

# Normalize age to [0,1] (assuming max age around 100)
age_labels_norm = age_labels / 100.0

# One-hot encode gender (0 or 1)
gender_labels_cat = to_categorical(gender_labels, num_classes=2)

# Split into train and validation sets
x_train, x_val, age_train, age_val, gender_train, gender_val = train_test_split(
    x, age_labels_norm, gender_labels_cat, test_size=0.2, random_state=42
)

# Build CNN model with two outputs: age (regression) and gender (classification)
inputs = Input(shape=(IMG_SIZE, IMG_SIZE, 3))

x = Conv2D(32, (3,3), activation='relu')(inputs)
x = MaxPooling2D()(x)
x = Conv2D(64, (3,3), activation='relu')(x)
x = MaxPooling2D()(x)
x = Conv2D(128, (3,3), activation='relu')(x)
x = MaxPooling2D()(x)
x = Flatten()(x)
x = Dense(128, activation='relu')(x)
x = Dropout(0.5)(x)

# Age regression output
age_output = Dense(1, activation='linear', name='age_output')(x)

# Gender classification output
gender_output = Dense(2, activation='softmax', name='gender_output')(x)

model = Model(inputs=inputs, outputs=[age_output, gender_output])

model.compile(
    optimizer=Adam(learning_rate=1e-4),
    loss={
        'age_output': 'mse',
        'gender_output': 'categorical_crossentropy'
    },
    metrics={
        'age_output': 'mae',
        'gender_output': 'accuracy'
    }
)

model.summary()

# Train the model
history = model.fit(
    x_train,
    {'age_output': age_train, 'gender_output': gender_train},
    validation_data=(x_val, {'age_output': age_val, 'gender_output': gender_val}),
    epochs=15,
    batch_size=64
)
# Save the trained model
model.save('models/age_gender_model.h5')
print("Model saved successfully at 'models/age_gender_model.h5'")