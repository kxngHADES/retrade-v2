import easyocr
import cv2
import time
import re
from typing import List, Tuple, Optional


class IDExtractor:
	def __init__(self, gpu: bool = False):
		self.reader = easyocr.Reader(['en'], gpu=gpu)

	def extract_text(self, img_path: str, max_dimension:int = 1500, min_confidence: float = 0.4) -> List[Tuple[str, float]]:
		img = cv2.imread(img_path)

		if img is None:
			raise FileNotFoundError(f"Could not load image at {img_path}")

		original_height, original_width = img.shape[:2]

		if original_width > max_dimension or original_height > max_dimension:
			scale = max_dimension / max(original_width, original_height)
			new_width = int(original_width * scale)
			new_height = int(original_height * scale)
			img = cv2.resize(img, (new_width, new_height))

		results = self.reader.readtext(img)

		filtered = [
			(detection[1], detection[2])
			for detection in results
			if detection[2] >= min_confidence
		]

		return filtered

	def find_id_number(self, texts: List[Tuple[str, float]]) -> Optional[Tuple[str, float]]:
		id_patterns = [
			r'^\d{13}$',
		]

		for text, confidence in texts:
			clean_text = text.replace(" ", "").replace("-", "")

			for pattern in id_patterns:
				if re.match(pattern, clean_text):
					return (clean_text, confidence)
		
		return None

	def process_image_for_id(self, img_path: str, max_dimension: int = 1500, min_confidence: float = 0.4) -> Optional[Tuple[str, float]]:
		texts = self.extract_text(img_path, max_dimension, min_confidence)
		return self.find_id_number(texts)

	def validate_sa_id(id_number: str) -> bool:
		if not id_number.isdigit() or len(id_number) != 13:
			return False

		digits = [int(d) for d in id_number]
		checksum = digits.pop()

		for i in range(len(digits) - 1, -1, -1):
			if (len(digits) - i) % 2 == 1:
				doubled = digits[i] * 2
				digits[i] = doubled if doubled < 10 else (doubled // 10) + (doubled % 10)

		total = sum(digits)
		expected = (10 - (total % 10)) % 10
		return checksum == expected