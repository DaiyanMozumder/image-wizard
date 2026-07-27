import unittest
import os
from PIL import Image
from imagewizard.engines.watermark import WatermarkEngine

class TestWatermarkEngine(unittest.TestCase):
    def setUp(self):
        self.main_img_path = 'test_main.jpg'
        self.wm_wide_path = 'test_wm_wide.png'
        self.wm_square_path = 'test_wm_square.png'

        # Create main image (1000x800)
        img = Image.new('RGB', (1000, 800), color='blue')
        img.save(self.main_img_path)

        # Create wide watermark (300x100 -> aspect ratio 3.0 > 1.5)
        wm_wide = Image.new('RGBA', (300, 100), color=(255, 0, 0, 255))
        wm_wide.save(self.wm_wide_path)

        # Create square watermark (100x100)
        wm_sq = Image.new('RGBA', (100, 100), color=(0, 255, 0, 255))
        wm_sq.save(self.wm_square_path)

    def tearDown(self):
        for path in [self.main_img_path, self.wm_wide_path, self.wm_square_path]:
            if os.path.exists(path):
                os.remove(path)

    def test_aspect_ratio_crop_wide_logo(self):
        image = Image.open(self.main_img_path)
        params = {
            'path': self.wm_wide_path,
            'position': 'bottom-right'
        }
        # Wide logo (300x100) aspect ratio = 3.0 > 1.5
        # Should crop to square (100x100) first, then scale relative to min(1000, 800) * 0.12 = 96
        result = WatermarkEngine.process(image, params)
        self.assertIsNotNone(result)
        self.assertEqual(result.size, (1000, 800))

    def test_smart_scaling_and_dynamic_margin(self):
        image = Image.open(self.main_img_path)
        # min dim is 800. size_ratio 0.12 -> max size 96
        # margin default = 3% of 800 = 24px (max(10, 24) -> 24)
        params = {
            'path': self.wm_square_path,
            'size_ratio': 0.20,
            'margin': 15
        }
        result = WatermarkEngine.process(image, params)
        self.assertIsNotNone(result)

    def test_missing_watermark_path(self):
        image = Image.open(self.main_img_path)
        result = WatermarkEngine.process(image, {'path': 'non_existent.png'})
        self.assertEqual(result, image)

if __name__ == '__main__':
    unittest.main()
