from PIL import Image, ImageDraw
import os

os.makedirs('icons', exist_ok=True)

for size in [192, 512]:
    img = Image.new('RGB', (size, size), '#0d0f14')
    draw = ImageDraw.Draw(img)
    
    # Orange circle background
    margin = size // 8
    draw.ellipse([margin, margin, size-margin, size-margin], fill='#e8622a')
    
    # "AG" text approximated with rectangles
    cx, cy = size // 2, size // 2
    bar_w = size // 8
    bar_h = size // 3
    
    # Left bar (A shape simplified)
    draw.rectangle([cx - bar_w*2, cy - bar_h//2, cx - bar_w, cy + bar_h//2], fill='white')
    # Right bar
    draw.rectangle([cx + bar_w, cy - bar_h//2, cx + bar_w*2, cy + bar_h//2], fill='white')
    # Cross bar
    draw.rectangle([cx - bar_w*2, cy - bar_w//2, cx + bar_w*2, cy + bar_w//2], fill='white')
    
    img.save(f'icons/icon-{size}.png')
    print(f'Generated icon-{size}.png')
