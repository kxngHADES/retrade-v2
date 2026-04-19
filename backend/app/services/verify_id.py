import cv2
import re
import pytesseract
import numpy as np
from collections import Counter
from pathlib import Path
from typing import Optional, Tuple


class IDExtractor:
    def __init__(self):
        pass

    def _get_candidates(self, img) -> list[str]:
        """Run multiple preprocessing + PSM combos and collect all 13-digit hits."""
        # Upscale first — tiny images (<500px wide) fail badly without this
        h, w = img.shape[:2]
        if w < 1000:
            scale = max(2, 1000 // w)
            img = cv2.resize(img, (w * scale, h * scale), interpolation=cv2.INTER_CUBIC)

        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

        pipelines = [
            gray,
            cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)[1],
            cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 31, 10),
            cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8)).apply(gray),
        ]

        candidates = []
        for processed in pipelines:
            for psm in [3, 6, 11, 12]:
                config = f'--oem 3 --psm {psm} -c tessedit_char_whitelist=0123456789'
                raw = pytesseract.image_to_string(processed, config=config)
                for token in re.split(r'\s+', raw):
                    clean = re.sub(r'[^0-9]', '', token)
                    if len(clean) == 13:
                        candidates.append(clean)
                    elif len(clean) > 13:
                        # Sliding window — catches cases where digits are merged
                        for i in range(len(clean) - 12):
                            candidates.append(clean[i:i + 13])

        return candidates

    def extract_id_number(self, img_path: str) -> Optional[Tuple[str, float]]:
        """Returns (id_number, confidence) or None. Confidence = fraction of pipelines that agreed."""
        img = cv2.imread(str(img_path))
        if img is None:
            raise FileNotFoundError(f"Could not load image at {img_path}")

        candidates = self._get_candidates(img)
        if not candidates:
            return None

        counts = Counter(candidates)
        best, freq = counts.most_common(1)[0]
        confidence = freq / len(candidates)

        return (best, confidence)

    @staticmethod
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