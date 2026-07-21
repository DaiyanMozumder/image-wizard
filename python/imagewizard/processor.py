import os
from PIL import Image, ImageOps
from .engines.resize import ResizeEngine
from .engines.watermark import WatermarkEngine

class ImageProcessor:
    def __init__(self, payload):
        self.payload = payload
        self.source = payload.get('source')
        self.destination = payload.get('destination')
        self.operations = payload.get('operations', [])
        self.options = payload.get('options', {})
        
    def execute(self):
        if not self.source or not os.path.exists(self.source):
            return {"success": False, "error": f"Source image not found: {self.source}"}
            
        if not self.destination:
            return {"success": False, "error": "Destination path not specified"}
            
        try:
            image = Image.open(self.source)
            
            exif_data = image.info.get('exif')
            preserve_metadata = self.options.get('preserveMetadata', False)
            
            for op in self.operations:
                op_type = op.get('type')
                
                if op_type == 'resize':
                    image = ResizeEngine.process(image, op)
                elif op_type == 'watermark':
                    image = WatermarkEngine.process(image, op)
                    
            save_kwargs = {}
            format_opt = self.options.get('format')
            if format_opt:
                if format_opt.lower() == 'jpg':
                    format_opt = 'JPEG'
                save_kwargs['format'] = format_opt.upper()
                
            quality = self.options.get('quality')
            if quality:
                save_kwargs['quality'] = quality
                
            if preserve_metadata and exif_data:
                save_kwargs['exif'] = exif_data
                
            target_format = save_kwargs.get('format', image.format).upper() if image.format else 'JPEG'
            if target_format == 'JPEG' and image.mode in ('RGBA', 'LA', 'P'):
                bg = Image.new("RGB", image.size, (255, 255, 255))
                if image.mode == 'P':
                    image = image.convert('RGBA')
                bg.paste(image, mask=image.split()[3] if len(image.split()) == 4 else None)
                image = bg
                
            try:
                image.save(self.destination, **save_kwargs)
            except Exception as save_err:
                if target_format == 'AVIF' and image.mode in ('RGBA', 'LA', 'P'):
                    bg = Image.new("RGB", image.size, (255, 255, 255))
                    if image.mode == 'P':
                        image = image.convert('RGBA')
                    bg.paste(image, mask=image.split()[3] if len(image.split()) == 4 else None)
                    image = bg
                    image.save(self.destination, **save_kwargs)
                else:
                    raise save_err
            
            return {
                "success": True, 
                "data": {
                    "destination": self.destination,
                    "width": image.width,
                    "height": image.height,
                    "format": format_opt if format_opt else image.format
                }
            }
            
        except Exception as e:
            return {"success": False, "error": str(e)}
