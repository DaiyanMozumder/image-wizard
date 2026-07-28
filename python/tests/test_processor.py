import unittest
import os
from PIL import Image
from imagewizard.processor import ImageProcessor

class TestImageProcessor(unittest.TestCase):
    def setUp(self):
        self.test_img_path = 'test_source.jpg'
        self.test_img_rgba_path = 'test_source_rgba.png'
        self.test_out_path = 'test_dest.jpg'
        self.test_out_png_path = 'test_dest_png.png'
        
        # Create a dummy image for testing
        img = Image.new('RGB', (800, 600), color = 'red')
        img.save(self.test_img_path)

        # Create a dummy transparent image for testing
        img_rgba = Image.new('RGBA', (100, 100), color = (255, 0, 0, 128))
        img_rgba.save(self.test_img_rgba_path)

    def tearDown(self):
        for path in [self.test_img_path, self.test_img_rgba_path, self.test_out_path, self.test_out_png_path]:
            if os.path.exists(path):
                os.remove(path)

    def test_resize_contain(self):
        payload = {
            'action': 'process',
            'source': self.test_img_path,
            'destination': self.test_out_path,
            'operations': [
                {'type': 'resize', 'width': 400, 'height': 400, 'fit': 'contain'}
            ],
            'options': {}
        }
        
        processor = ImageProcessor(payload)
        result = processor.execute()
        
        self.assertTrue(result['success'])
        
        # Verify dimensions
        out_img = Image.open(self.test_out_path)
        self.assertEqual(400, out_img.width)
        # Height should maintain aspect ratio: 800x600 -> 400x300
        self.assertEqual(300, out_img.height)
        out_img.close()

    def test_target_format_from_extension(self):
        payload = {
            'action': 'process',
            'source': self.test_img_path,
            'destination': self.test_out_png_path,
            'operations': [],
            'options': {}
        }
        processor = ImageProcessor(payload)
        
        # We can construct target_format using processor logic
        # target_format should be PNG since destination ends in .png
        result = processor.execute()
        self.assertTrue(result['success'])
        
        out_img = Image.open(self.test_out_png_path)
        self.assertEqual(out_img.format, 'PNG')
        out_img.close()

    def test_transparent_rgba_to_jpeg(self):
        payload = {
            'action': 'process',
            'source': self.test_img_rgba_path,
            'destination': self.test_out_path, # .jpg extension
            'operations': [],
            'options': {}
        }
        processor = ImageProcessor(payload)
        result = processor.execute()
        
        self.assertTrue(result['success'], result.get('error'))
        out_img = Image.open(self.test_out_path)
        self.assertEqual(out_img.mode, 'RGB')
        out_img.close()

if __name__ == '__main__':
    unittest.main()
