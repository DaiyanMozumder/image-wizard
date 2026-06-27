import os
from PIL import Image

class WatermarkEngine:
    @staticmethod
    def process(image, params):
        watermark_path = params.get('path')
        if not watermark_path or not os.path.exists(watermark_path):
            return image
            
        position = params.get('position', 'bottom-right')
        opacity = float(params.get('opacity', 1.0))
        margin = int(params.get('margin', 10))
        
        try:
            watermark = Image.open(watermark_path).convert("RGBA")
            
            if opacity < 1.0:
                alpha = watermark.split()[3]
                alpha = alpha.point(lambda p: p * opacity)
                watermark.putalpha(alpha)
                
            original_mode = image.mode
            if image.mode != 'RGBA':
                image = image.convert('RGBA')
                
            x = margin
            y = margin
            
            if position == 'top-left':
                pass
            elif position == 'top-center':
                x = (image.width - watermark.width) // 2
            elif position == 'top-right':
                x = image.width - watermark.width - margin
            elif position == 'middle-left':
                y = (image.height - watermark.height) // 2
            elif position == 'center':
                x = (image.width - watermark.width) // 2
                y = (image.height - watermark.height) // 2
            elif position == 'middle-right':
                x = image.width - watermark.width - margin
                y = (image.height - watermark.height) // 2
            elif position == 'bottom-left':
                y = image.height - watermark.height - margin
            elif position == 'bottom-center':
                x = (image.width - watermark.width) // 2
                y = image.height - watermark.height - margin
            elif position == 'bottom-right':
                x = image.width - watermark.width - margin
                y = image.height - watermark.height - margin
            else:
                try:
                    coords = position.split(',')
                    if len(coords) == 2:
                        x = int(coords[0])
                        y = int(coords[1])
                except:
                    pass
                    
            transparent = Image.new('RGBA', image.size, (0, 0, 0, 0))
            transparent.paste(watermark, (x, y), mask=watermark)
            image = Image.alpha_composite(image, transparent)
            
            if original_mode != 'RGBA':
                bg = Image.new("RGB", image.size, (255, 255, 255))
                bg.paste(image, mask=image.split()[3])
                return bg
                
            return image
        except Exception as e:
            return image
