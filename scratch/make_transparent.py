import math
from PIL import Image

def process_emblem_transparent(input_path, output_path):
    img = Image.open(input_path).convert('RGBA')
    width, height = img.size
    pix = img.load()

    # Center coordinates
    cx, cy = width / 2.0, height / 2.0
    max_radius = min(width, height) / 2.0

    for x in range(width):
        for y in range(height):
            r, g, b, a = pix[x, y]
            
            # Distance from center
            dist = math.hypot(x - cx, y - cy)
            
            # Calculate brightness/luminance
            luminance = 0.299 * r + 0.587 * g + 0.114 * b
            
            # Check if pixel is part of dark background
            # Dark background pixels typically have low luminance and dark tone
            if luminance < 55 and r < 70 and g < 75 and b < 85:
                # Transparent alpha
                pix[x, y] = (r, g, b, 0)
            elif luminance < 85 and r < 90 and g < 95 and b < 105:
                # Smooth alpha transition at borders (anti-aliasing)
                alpha_factor = (luminance - 55) / (85 - 55)
                new_a = int(255 * max(0.0, min(1.0, alpha_factor)))
                pix[x, y] = (r, g, b, new_a)

    img.save(output_path, 'PNG')
    print(f"Saved transparent image to {output_path}")

process_emblem_transparent('public/images/ak-emblem.png', 'public/images/ak-emblem.png')
process_emblem_transparent('public/images/ak-logo.png', 'public/images/ak-logo.png')
