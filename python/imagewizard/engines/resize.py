from PIL import Image, ImageOps

class ResizeEngine:
    @staticmethod
    def process(image, params):
        width = params.get('width')
        height = params.get('height')
        fit = params.get('fit', 'contain')
        
        if not width and not height:
            return image
            
        # If one dimension is missing, maintain aspect ratio
        if not height:
            ratio = width / float(image.size[0])
            height = int((float(image.size[1]) * float(ratio)))
        if not width:
            ratio = height / float(image.size[1])
            width = int((float(image.size[0]) * float(ratio)))
            
        target_size = (width, height)
        
        if fit == 'crop' or fit == 'cover':
            return ImageOps.fit(image, target_size, method=Image.Resampling.LANCZOS, centering=(0.5, 0.5))
        elif fit == 'contain':
            img = image.copy()
            img.thumbnail(target_size, Image.Resampling.LANCZOS)
            return img
        elif fit == 'stretch':
            return image.resize(target_size, Image.Resampling.LANCZOS)
        elif fit == 'pad' or fit == 'letterbox':
            img = image.copy()
            img.thumbnail(target_size, Image.Resampling.LANCZOS)
            # Create a new image with transparent or white background
            new_img = Image.new(
                "RGBA" if image.mode in ('RGBA', 'LA') or (image.mode == 'P' and 'transparency' in image.info) else "RGB", 
                target_size, 
                (255, 255, 255, 0)
            )
            paste_pos = ((target_size[0] - img.size[0]) // 2, (target_size[1] - img.size[1]) // 2)
            new_img.paste(img, paste_pos)
            return new_img
            
        # Default to stretch if fit is unknown
        return image.resize(target_size, Image.Resampling.LANCZOS)
