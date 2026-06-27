import unittest
import os
from PIL import Image
from imagewizard.processor import ImageProcessor

class TestImageProcessor(unittest.TestCase):
    def setUp(self):
        self.test_img_path = 'test_source.jpg'
        self.test_out_path = 'test_dest.jpg'
        
        # Create a dummy image for testing
        img = Image.new('RGB', (800, 600), color = 'red')
        img.save(self.test_img_path)

    def tearDown(self):
        if os.path.exists(self.test_img_path):
            os.remove(self.test_img_path)
        if os.path.exists(self.test_out_path):
            os.remove(self.test_out_path)

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

if __name__ == '__main__':
    unittest.main()
