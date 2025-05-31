import cv2
import numpy as np
import mediapipe as mp
from tkinter import Tk, filedialog

def get_head_pose(landmarks, image_shape):
    image_height, image_width = image_shape

    image_points = np.array([
        landmarks[33][:2],   # Nose tip
        landmarks[263][:2],  # Right eye outer
        landmarks[362][:2],  # Right eye inner
        landmarks[133][:2],  # Left eye outer
        landmarks[173][:2],  # Between the eyes
        landmarks[168][:2],  # Nose bridge
    ], dtype="double")

    model_points = np.array([
        [0.0, 0.0, 0.0],               # Nose tip
        [-30.0, -125.0, -30.0],        # Right eye outer
        [-60.0, -125.0, -30.0],        # Right eye inner
        [30.0, -125.0, -30.0],         # Left eye outer
        [0.0, -110.0, -25.0],          # Between eyes
        [0.0, -80.0, -15.0],           # Nose bridge
    ])

    focal_length = image_width
    center = (image_width / 2, image_height / 2)
    camera_matrix = np.array([
        [focal_length, 0, center[0]],
        [0, focal_length, center[1]],
        [0, 0, 1]
    ], dtype="double")

    dist_coeffs = np.zeros((4, 1))
    success, rotation_vector, translation_vector = cv2.solvePnP(
        model_points, image_points, camera_matrix, dist_coeffs
    )

    rotation_mat, _ = cv2.Rodrigues(rotation_vector)
    pose_mat = cv2.hconcat((rotation_mat, translation_vector))
    _, _, _, _, _, _, euler_angles = cv2.decomposeProjectionMatrix(pose_mat)

    raw_yaw = euler_angles[1][0]
    pitch = euler_angles[0][0]
    roll = euler_angles[2][0]

    yaw = -raw_yaw
    roll = (roll + 180) % 360 - 180

    return yaw, pitch, roll


# --- Interpretation Function ---
def interpret_head_pose(yaw, pitch, roll):
    abs_yaw = abs(yaw)
    abs_pitch = abs(pitch)
    abs_roll = abs(roll)

    if abs_yaw < 20 and abs_pitch < 20 and abs_roll < 20:
        return "Straight"

    if abs_pitch > abs_yaw and abs_pitch > abs_roll:
        if pitch > 15:
            return "Looking Down"
        elif pitch < -15:
            return "Looking Up"
    elif abs_yaw > abs_pitch and abs_yaw > abs_roll:
        if yaw > 20:
            return "Looking Right"
        elif yaw < -20:
            return "Looking Left"
    elif abs_roll > 25:
        if roll > 0:
            return "Head Tilted Right"
        else:
            return "Head Tilted Left"

    return "Uncertain"


def recognize_head_pose(image_path):
    mp_face_mesh = mp.solutions.face_mesh
    face_mesh = mp_face_mesh.FaceMesh(static_image_mode=True)

    image = cv2.imread(image_path)
    if image is None:
        print("Error loading image.")
        return

    img_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    results = face_mesh.process(img_rgb)

    if not results.multi_face_landmarks:
        print(" No face detected.")
        return

    landmarks = results.multi_face_landmarks[0]
    height, width = image.shape[:2]
    landmark_coords = [(p.x * width, p.y * height, p.z * width) for p in landmarks.landmark]

    try:
        yaw, pitch, roll = get_head_pose(landmark_coords, (height, width))
        position = interpret_head_pose(yaw, pitch, roll)
        print(f"\nHead Position: {position}")
        print(f" Yaw: {yaw:.2f}°, Pitch: {pitch:.2f}°, Roll: {roll:.2f}°")
    except Exception as e:
        print(" Pose estimation failed:", str(e))


def select_image_and_predict():
    Tk().withdraw()
    image_path = filedialog.askopenfilename(
        title="Select an image for head pose estimation",
        filetypes=[("Image files", "*.jpg *.jpeg *.png")]
    )

    if not image_path:
        print(" No image selected.")
        return

    print(f"\n Selected file: {image_path}")
    recognize_head_pose(image_path)


if __name__ == "__main__":
    select_image_and_predict()
