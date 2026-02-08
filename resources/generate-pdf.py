#!/usr/bin/env python3
"""
Stitching Practice Guide PDF Generator
Generates a clean, professional PDF for CuttingMaster
"""

from reportlab.lib.pagesizes import A4
from reportlab.lib.units import mm, cm
from reportlab.lib.colors import HexColor, white, black
from reportlab.pdfgen import canvas
from reportlab.lib.styles import getSampleStyleSheet
from reportlab.platypus import Paragraph
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.lib.utils import ImageReader
import os

# Colors
RED = HexColor('#dd2a2a')
DARK_RED = HexColor('#c41e1e')
GRAY = HexColor('#666666')
LIGHT_GRAY = HexColor('#999999')
DARK = HexColor('#333333')
GREEN = HexColor('#2e7d32')
ORANGE = HexColor('#ef6c00')
PINK = HexColor('#c2185b')
BG_GREEN = HexColor('#e8f5e9')
BG_ORANGE = HexColor('#fff3e0')
BG_PINK = HexColor('#fce4ec')

# Page dimensions
PAGE_WIDTH, PAGE_HEIGHT = A4
MARGIN = 15 * mm

def draw_logo(c, x, y, width=50*mm):
    """Draw the CuttingMaster logo from PNG file"""
    # Use the high-res PNG logo
    logo_path = os.path.join(os.path.dirname(__file__), '..', 'images', 'logo2.png')

    # Original image is 912 x 230
    aspect_ratio = 912 / 230
    height = width / aspect_ratio

    # Draw the image
    c.drawImage(logo_path, x, y, width=width, height=height, mask='auto')

def draw_header(c, page_num, total_pages, skill_level="Beginner"):
    """Draw page header with logo, skill badge, and page number"""
    y = PAGE_HEIGHT - MARGIN - 8*mm

    # Logo
    draw_logo(c, MARGIN, y - 2*mm, width=40*mm)

    # Skill badge
    badge_colors = {
        "Beginner": (BG_GREEN, GREEN),
        "Intermediate": (BG_ORANGE, ORANGE),
        "Advanced": (BG_PINK, PINK)
    }
    bg_color, text_color = badge_colors.get(skill_level, (BG_GREEN, GREEN))

    badge_x = PAGE_WIDTH / 2 - 15*mm
    c.setFillColor(bg_color)
    c.roundRect(badge_x, y - 1*mm, 30*mm, 6*mm, 3*mm, fill=1, stroke=0)
    c.setFillColor(text_color)
    c.setFont("Helvetica-Bold", 8)
    c.drawCentredString(badge_x + 15*mm, y + 0.5*mm, skill_level.upper())

    # Page number
    c.setFillColor(GRAY)
    c.setFont("Helvetica", 9)
    c.drawRightString(PAGE_WIDTH - MARGIN, y + 1*mm, f"Page {page_num} of {total_pages}")

    # Header line
    c.setStrokeColor(RED)
    c.setLineWidth(2)
    c.line(MARGIN, y - 5*mm, PAGE_WIDTH - MARGIN, y - 5*mm)

    return y - 12*mm

def draw_footer(c):
    """Draw page footer"""
    c.setFillColor(LIGHT_GRAY)
    c.setFont("Helvetica", 8)
    c.drawCentredString(PAGE_WIDTH / 2, MARGIN, "www.cuttingmaster.in | Stitching Practice Guide")

def draw_title(c, y, title, subtitle=None):
    """Draw page title and subtitle"""
    c.setFillColor(RED)
    c.setFont("Helvetica-Bold", 20)
    c.drawCentredString(PAGE_WIDTH / 2, y, title)

    if subtitle:
        c.setFillColor(GRAY)
        c.setFont("Helvetica", 11)
        c.drawCentredString(PAGE_WIDTH / 2, y - 8*mm, subtitle)
        return y - 18*mm
    return y - 12*mm

def draw_practice_box(c, x, y, width, height):
    """Draw a practice area box"""
    c.setStrokeColor(HexColor('#dddddd'))
    c.setLineWidth(1)
    c.rect(x, y, width, height, fill=0, stroke=1)

    # Inner dashed border
    c.setStrokeColor(HexColor('#cccccc'))
    c.setDash(3, 3)
    c.rect(x + 2*mm, y + 2*mm, width - 4*mm, height - 4*mm, fill=0, stroke=1)
    c.setDash()

def draw_guide_line(c, x1, y1, x2, y2):
    """Draw a dashed red guide line"""
    c.setStrokeColor(RED)
    c.setLineWidth(1.5)
    c.setDash(6, 3)
    c.line(x1, y1, x2, y2)
    c.setDash()

def draw_solid_line(c, x1, y1, x2, y2, color=DARK):
    """Draw a solid line"""
    c.setStrokeColor(color)
    c.setLineWidth(2)
    c.line(x1, y1, x2, y2)

# Page content functions

def page_cover(c):
    """Draw cover page"""
    # Large logo centered
    c.setFillColor(RED)
    c.setFont("Helvetica-Bold", 36)
    c.drawCentredString(PAGE_WIDTH / 2, PAGE_HEIGHT - 80*mm, "CuttingMaster")

    # Scissor icon (simplified)
    cx = PAGE_WIDTH / 2
    cy = PAGE_HEIGHT - 110*mm
    c.setFillColor(RED)
    # Draw a simple scissor representation
    c.setLineWidth(3)
    c.setStrokeColor(RED)
    c.line(cx - 20*mm, cy + 10*mm, cx, cy - 5*mm)
    c.line(cx + 20*mm, cy + 10*mm, cx, cy - 5*mm)
    c.circle(cx - 15*mm, cy + 15*mm, 5*mm, fill=0, stroke=1)
    c.circle(cx + 15*mm, cy + 15*mm, 5*mm, fill=0, stroke=1)

    # Title
    c.setFillColor(DARK)
    c.setFont("Helvetica", 28)
    c.drawCentredString(PAGE_WIDTH / 2, PAGE_HEIGHT - 145*mm, "Stitching Practice Guide")

    c.setFillColor(GRAY)
    c.setFont("Helvetica", 14)
    c.drawCentredString(PAGE_WIDTH / 2, PAGE_HEIGHT - 158*mm, "Master Your Sewing Machine Skills")

    # Description
    c.setFont("Helvetica", 11)
    desc_y = PAGE_HEIGHT - 180*mm
    lines = [
        "Printable worksheets to develop precise stitching control.",
        "Follow the dashed red guide lines to practice straight lines,",
        "curves, corners, and blouse-specific patterns."
    ]
    for line in lines:
        c.drawCentredString(PAGE_WIDTH / 2, desc_y, line)
        desc_y -= 5*mm

    # Skill level boxes
    box_y = PAGE_HEIGHT - 220*mm
    box_width = 45*mm
    box_height = 22*mm
    gap = 10*mm
    start_x = (PAGE_WIDTH - 3*box_width - 2*gap) / 2

    levels = [
        ("Beginner", "Pages 2-5", BG_GREEN, GREEN),
        ("Intermediate", "Pages 6-9", BG_ORANGE, ORANGE),
        ("Advanced", "Pages 10-12", BG_PINK, PINK)
    ]

    for i, (level, pages, bg, fg) in enumerate(levels):
        x = start_x + i * (box_width + gap)
        c.setFillColor(bg)
        c.roundRect(x, box_y, box_width, box_height, 3*mm, fill=1, stroke=0)
        c.setFillColor(fg)
        c.setFont("Helvetica-Bold", 11)
        c.drawCentredString(x + box_width/2, box_y + 13*mm, level)
        c.setFont("Helvetica", 9)
        c.setFillColor(GRAY)
        c.drawCentredString(x + box_width/2, box_y + 5*mm, pages)

    # Tips box
    tip_y = PAGE_HEIGHT - 265*mm
    tip_width = 130*mm
    tip_x = (PAGE_WIDTH - tip_width) / 2
    c.setFillColor(HexColor('#fff8e1'))
    c.rect(tip_x, tip_y, tip_width, 18*mm, fill=1, stroke=0)
    c.setStrokeColor(HexColor('#ffc107'))
    c.setLineWidth(3)
    c.line(tip_x, tip_y, tip_x, tip_y + 18*mm)

    c.setFillColor(HexColor('#f57c00'))
    c.setFont("Helvetica-Bold", 9)
    c.drawString(tip_x + 5*mm, tip_y + 11*mm, "How to Use:")
    c.setFillColor(DARK)
    c.setFont("Helvetica", 9)
    c.drawString(tip_x + 5*mm, tip_y + 4*mm, "Print on A4 paper. Practice stitching along dashed red")
    c.drawString(tip_x + 5*mm, tip_y - 2*mm, "lines (without thread first). Focus on steady speed.")

    # Footer
    c.setFillColor(LIGHT_GRAY)
    c.setFont("Helvetica", 9)
    c.drawCentredString(PAGE_WIDTH / 2, MARGIN + 5*mm, "www.cuttingmaster.in | Free Stitching Practice Guide")

def page_horizontal_lines(c):
    """Page 2: Horizontal lines practice"""
    y = draw_header(c, 2, 12, "Beginner")
    y = draw_title(c, y, "Horizontal Lines Practice",
                   "Stitch along each dashed line from left to right. Maintain consistent speed.")

    # Practice area
    box_x = MARGIN + 5*mm
    box_width = PAGE_WIDTH - 2*MARGIN - 10*mm
    box_height = y - MARGIN - 15*mm
    box_y = MARGIN + 10*mm

    draw_practice_box(c, box_x, box_y, box_width, box_height)

    # Draw horizontal guide lines
    line_spacing = 7*mm
    num_lines = int((box_height - 10*mm) / line_spacing)

    for i in range(num_lines):
        line_y = box_y + 5*mm + i * line_spacing
        draw_guide_line(c, box_x + 5*mm, line_y, box_x + box_width - 5*mm, line_y)

    draw_footer(c)

def page_vertical_lines(c):
    """Page 3: Vertical lines practice"""
    y = draw_header(c, 3, 12, "Beginner")
    y = draw_title(c, y, "Vertical Lines Practice",
                   "Stitch along each dashed line from top to bottom. Keep fabric aligned.")

    box_x = MARGIN + 5*mm
    box_width = PAGE_WIDTH - 2*MARGIN - 10*mm
    box_height = y - MARGIN - 15*mm
    box_y = MARGIN + 10*mm

    draw_practice_box(c, box_x, box_y, box_width, box_height)

    # Draw vertical guide lines
    line_spacing = 6*mm
    num_lines = int((box_width - 10*mm) / line_spacing)

    for i in range(num_lines):
        line_x = box_x + 5*mm + i * line_spacing
        draw_guide_line(c, line_x, box_y + 5*mm, line_x, box_y + box_height - 5*mm)

    draw_footer(c)

def page_diagonal_lines(c):
    """Page 4: Diagonal lines practice"""
    y = draw_header(c, 4, 12, "Beginner")
    y = draw_title(c, y, "Diagonal Lines Practice",
                   "Practice stitching at 45-degree angles. Essential for dart stitching.")

    box_x = MARGIN + 5*mm
    box_width = PAGE_WIDTH - 2*MARGIN - 10*mm
    box_height = y - MARGIN - 15*mm
    box_y = MARGIN + 10*mm

    draw_practice_box(c, box_x, box_y, box_width, box_height)

    # Left-to-right diagonals (top-left to bottom-right)
    spacing = 12*mm
    for i in range(15):
        start_x = box_x + 5*mm
        start_y = box_y + box_height - 5*mm - i * spacing
        length = min(box_width - 10*mm, box_height - 10*mm - i * spacing)
        if length > 20*mm and start_y > box_y + 5*mm:
            draw_guide_line(c, start_x, start_y, start_x + length, start_y - length)

    # Right-to-left diagonals (top-right to bottom-left)
    for i in range(15):
        start_x = box_x + box_width - 5*mm
        start_y = box_y + box_height - 5*mm - i * spacing
        length = min(box_width - 10*mm, box_height - 10*mm - i * spacing)
        if length > 20*mm and start_y > box_y + 5*mm:
            draw_guide_line(c, start_x, start_y, start_x - length, start_y - length)

    draw_footer(c)

def page_seam_allowance(c):
    """Page 5: Seam allowance practice"""
    y = draw_header(c, 5, 12, "Beginner")
    y = draw_title(c, y, "Parallel Lines - Seam Allowance",
                   "Practice maintaining consistent distance between parallel lines (1cm apart).")

    box_x = MARGIN + 5*mm
    box_width = PAGE_WIDTH - 2*MARGIN - 10*mm
    box_height = y - MARGIN - 15*mm
    box_y = MARGIN + 10*mm

    draw_practice_box(c, box_x, box_y, box_width, box_height)

    # Draw pairs of lines (edge + stitch line)
    pair_spacing = 18*mm
    num_pairs = int((box_height - 15*mm) / pair_spacing)

    c.setFont("Helvetica", 7)
    for i in range(num_pairs):
        line_y = box_y + 10*mm + i * pair_spacing
        # Edge line (solid)
        draw_solid_line(c, box_x + 5*mm, line_y, box_x + box_width - 20*mm, line_y)
        c.setFillColor(GRAY)
        c.drawString(box_x + box_width - 18*mm, line_y - 1*mm, "Edge")

        # Stitch line (dashed, 1cm below)
        draw_guide_line(c, box_x + 5*mm, line_y + 10*mm, box_x + box_width - 20*mm, line_y + 10*mm)
        c.drawString(box_x + box_width - 18*mm, line_y + 9*mm, "Stitch")

    draw_footer(c)

def page_waves(c):
    """Page 6: Gentle waves practice"""
    y = draw_header(c, 6, 12, "Intermediate")
    y = draw_title(c, y, "Gentle Waves Practice",
                   "Follow the curved lines smoothly. Practice for princess seams and curved edges.")

    box_x = MARGIN + 5*mm
    box_width = PAGE_WIDTH - 2*MARGIN - 10*mm
    box_height = y - MARGIN - 15*mm
    box_y = MARGIN + 10*mm

    draw_practice_box(c, box_x, box_y, box_width, box_height)

    # Draw wave patterns
    c.setStrokeColor(RED)
    c.setLineWidth(1.5)
    c.setDash(6, 3)

    wave_spacing = 10*mm
    num_waves = int((box_height - 15*mm) / wave_spacing)
    wave_width = 25*mm
    amplitude = 4*mm

    for i in range(num_waves):
        base_y = box_y + 8*mm + i * wave_spacing
        path = c.beginPath()
        path.moveTo(box_x + 5*mm, base_y)

        x = box_x + 5*mm
        while x < box_x + box_width - 5*mm:
            # Draw sine-like curve
            path.curveTo(x + wave_width/4, base_y + amplitude,
                        x + wave_width/2, base_y + amplitude,
                        x + wave_width/2, base_y)
            path.curveTo(x + 3*wave_width/4, base_y - amplitude,
                        x + wave_width, base_y - amplitude,
                        x + wave_width, base_y)
            x += wave_width

        c.drawPath(path, fill=0, stroke=1)

    c.setDash()
    draw_footer(c)

def page_s_curves(c):
    """Page 7: S-curve practice"""
    y = draw_header(c, 7, 12, "Intermediate")
    y = draw_title(c, y, "S-Curve Practice",
                   "Master smooth direction changes. Essential for side seams and waist shaping.")

    box_x = MARGIN + 5*mm
    box_width = PAGE_WIDTH - 2*MARGIN - 10*mm
    box_height = y - MARGIN - 15*mm
    box_y = MARGIN + 10*mm

    draw_practice_box(c, box_x, box_y, box_width, box_height)

    c.setStrokeColor(RED)
    c.setLineWidth(1.5)
    c.setDash(6, 3)

    curve_spacing = 18*mm
    num_curves = int((box_height - 20*mm) / curve_spacing)

    for i in range(num_curves):
        base_y = box_y + 15*mm + i * curve_spacing
        path = c.beginPath()

        x = box_x + 10*mm
        path.moveTo(x, base_y)

        segment_width = (box_width - 20*mm) / 4

        # S-curve pattern
        for j in range(4):
            if j % 2 == 0:
                path.curveTo(x + segment_width/2, base_y + 8*mm,
                            x + segment_width/2, base_y + 8*mm,
                            x + segment_width, base_y)
            else:
                path.curveTo(x + segment_width/2, base_y - 8*mm,
                            x + segment_width/2, base_y - 8*mm,
                            x + segment_width, base_y)
            x += segment_width

        c.drawPath(path, fill=0, stroke=1)

    c.setDash()
    draw_footer(c)

def page_corners(c):
    """Page 8: 90-degree corners practice"""
    y = draw_header(c, 8, 12, "Intermediate")
    y = draw_title(c, y, "90-Degree Corners",
                   "Practice pivoting at corners. Essential for necklines and square shapes.")

    box_x = MARGIN + 5*mm
    box_width = PAGE_WIDTH - 2*MARGIN - 10*mm
    box_height = y - MARGIN - 15*mm
    box_y = MARGIN + 10*mm

    draw_practice_box(c, box_x, box_y, box_width, box_height)

    # Draw zigzag corner patterns
    row_height = 30*mm
    num_rows = int((box_height - 20*mm) / row_height)

    for row in range(num_rows):
        base_y = box_y + box_height - 15*mm - row * row_height
        x = box_x + 10*mm

        c.setStrokeColor(RED)
        c.setLineWidth(1.5)
        c.setDash(6, 3)

        path = c.beginPath()
        path.moveTo(x, base_y)

        step = 25*mm
        going_down = True
        while x < box_x + box_width - 15*mm:
            if going_down:
                path.lineTo(x, base_y - 20*mm)
                path.lineTo(x + step, base_y - 20*mm)
            else:
                path.lineTo(x, base_y)
                path.lineTo(x + step, base_y)
            x += step
            going_down = not going_down

        c.drawPath(path, fill=0, stroke=1)
        c.setDash()

    draw_footer(c)

def page_circles(c):
    """Page 9: Circles and ovals practice"""
    y = draw_header(c, 9, 12, "Intermediate")
    y = draw_title(c, y, "Circles and Ovals",
                   "Practice continuous curved stitching. Important for decorative elements.")

    box_x = MARGIN + 5*mm
    box_width = PAGE_WIDTH - 2*MARGIN - 10*mm
    box_height = y - MARGIN - 15*mm
    box_y = MARGIN + 10*mm

    draw_practice_box(c, box_x, box_y, box_width, box_height)

    c.setStrokeColor(RED)
    c.setLineWidth(1.5)
    c.setDash(6, 3)

    # Draw circles
    circle_radius = 12*mm
    cols = 4
    rows = 5

    h_spacing = (box_width - 20*mm) / cols
    v_spacing = (box_height - 20*mm) / rows

    for row in range(rows):
        for col in range(cols):
            cx = box_x + 15*mm + col * h_spacing + h_spacing/2
            cy = box_y + 15*mm + row * v_spacing + v_spacing/2

            if row % 2 == 0:
                # Circle
                c.circle(cx, cy, circle_radius, fill=0, stroke=1)
            else:
                # Oval
                c.ellipse(cx - 15*mm, cy - 8*mm, cx + 15*mm, cy + 8*mm, fill=0, stroke=1)

    c.setDash()
    draw_footer(c)

def page_necklines(c):
    """Page 10: Neckline curves practice"""
    y = draw_header(c, 10, 12, "Advanced")
    y = draw_title(c, y, "Neckline Curves",
                   "Practice the curved shapes used in blouse necklines.")

    box_x = MARGIN + 5*mm
    box_width = PAGE_WIDTH - 2*MARGIN - 10*mm
    box_height = y - MARGIN - 15*mm
    box_y = MARGIN + 10*mm

    draw_practice_box(c, box_x, box_y, box_width, box_height)

    c.setStrokeColor(RED)
    c.setLineWidth(1.5)
    c.setDash(6, 3)

    section_height = (box_height - 30*mm) / 4

    # Section labels
    c.setDash()
    c.setFillColor(GRAY)
    c.setFont("Helvetica-Bold", 9)

    sections = ["Round Neckline", "V-Neckline", "Boat Neckline", "Sweetheart Neckline"]

    for i, section in enumerate(sections):
        section_y = box_y + box_height - 12*mm - i * section_height
        c.drawString(box_x + 8*mm, section_y, section)

        c.setStrokeColor(RED)
        c.setDash(6, 3)

        # Draw 2 practice curves per section
        for j in range(2):
            curve_y = section_y - 12*mm - j * 15*mm
            cx = box_x + box_width / 2

            path = c.beginPath()
            if section == "Round Neckline":
                # U-shape curve
                path.moveTo(cx - 60*mm, curve_y)
                path.curveTo(cx - 30*mm, curve_y - 25*mm, cx + 30*mm, curve_y - 25*mm, cx + 60*mm, curve_y)
            elif section == "V-Neckline":
                # V-shape
                path.moveTo(cx - 60*mm, curve_y)
                path.lineTo(cx, curve_y - 25*mm)
                path.lineTo(cx + 60*mm, curve_y)
            elif section == "Boat Neckline":
                # Shallow curve
                path.moveTo(cx - 60*mm, curve_y)
                path.curveTo(cx - 20*mm, curve_y - 8*mm, cx + 20*mm, curve_y - 8*mm, cx + 60*mm, curve_y)
            else:  # Sweetheart
                # Heart-shaped top
                path.moveTo(cx - 60*mm, curve_y)
                path.curveTo(cx - 40*mm, curve_y + 10*mm, cx - 20*mm, curve_y - 5*mm, cx, curve_y - 15*mm)
                path.curveTo(cx + 20*mm, curve_y - 5*mm, cx + 40*mm, curve_y + 10*mm, cx + 60*mm, curve_y)

            c.drawPath(path, fill=0, stroke=1)

        c.setDash()

    draw_footer(c)

def page_armholes(c):
    """Page 11: Armhole curves practice"""
    y = draw_header(c, 11, 12, "Advanced")
    y = draw_title(c, y, "Armhole Curves",
                   "Practice the complex curves found in armhole construction.")

    box_x = MARGIN + 5*mm
    box_width = PAGE_WIDTH - 2*MARGIN - 10*mm
    box_height = y - MARGIN - 15*mm
    box_y = MARGIN + 10*mm

    draw_practice_box(c, box_x, box_y, box_width, box_height)

    c.setStrokeColor(RED)
    c.setLineWidth(1.5)
    c.setDash(6, 3)

    # Labels
    c.setDash()
    c.setFillColor(GRAY)
    c.setFont("Helvetica-Bold", 9)

    # Front armhole section
    section_y = box_y + box_height - 12*mm
    c.drawString(box_x + 8*mm, section_y, "Front Armhole")

    # Draw front armhole curves (3 rows x 3 cols)
    c.setStrokeColor(RED)
    c.setDash(6, 3)

    for row in range(3):
        for col in range(3):
            cx = box_x + 35*mm + col * 55*mm
            cy = section_y - 35*mm - row * 50*mm

            # Front armhole curve (deeper curve)
            path = c.beginPath()
            path.moveTo(cx - 20*mm, cy + 25*mm)
            path.curveTo(cx - 20*mm, cy, cx, cy - 10*mm, cx + 20*mm, cy + 25*mm)
            c.drawPath(path, fill=0, stroke=1)

    # Back armhole section
    c.setDash()
    c.setFillColor(GRAY)
    back_y = box_y + box_height / 2 - 5*mm
    c.drawString(box_x + 8*mm, back_y, "Back Armhole")

    c.setStrokeColor(RED)
    c.setDash(6, 3)

    for row in range(2):
        for col in range(3):
            cx = box_x + 35*mm + col * 55*mm
            cy = back_y - 35*mm - row * 50*mm

            # Back armhole curve (shallower)
            path = c.beginPath()
            path.moveTo(cx - 20*mm, cy + 20*mm)
            path.curveTo(cx - 15*mm, cy + 5*mm, cx + 15*mm, cy + 5*mm, cx + 20*mm, cy + 20*mm)
            c.drawPath(path, fill=0, stroke=1)

    c.setDash()
    draw_footer(c)

def page_darts(c):
    """Page 12: Dart practice"""
    y = draw_header(c, 12, 12, "Advanced")
    y = draw_title(c, y, "Dart Practice",
                   "Master dart stitching - tapering to a point for bust, waist, and shoulder darts.")

    box_x = MARGIN + 5*mm
    box_width = PAGE_WIDTH - 2*MARGIN - 10*mm
    box_height = y - MARGIN - 15*mm
    box_y = MARGIN + 10*mm

    draw_practice_box(c, box_x, box_y, box_width, box_height)

    section_height = (box_height - 25*mm) / 4

    dart_types = [
        ("Bust Dart - Stitch from wide end to point", "horizontal"),
        ("Waist Dart - Vertical tapering", "vertical"),
        ("French Dart - Diagonal", "diagonal"),
        ("Curved Dart", "curved")
    ]

    for i, (label, dart_type) in enumerate(dart_types):
        section_y = box_y + box_height - 10*mm - i * section_height

        # Label
        c.setDash()
        c.setFillColor(GRAY)
        c.setFont("Helvetica-Bold", 8)
        c.drawString(box_x + 8*mm, section_y, label)

        c.setStrokeColor(RED)
        c.setLineWidth(1.5)
        c.setDash(6, 3)

        # Draw 4 darts per row
        dart_spacing = (box_width - 20*mm) / 4

        for j in range(4):
            dart_x = box_x + 20*mm + j * dart_spacing
            dart_y = section_y - 15*mm

            if dart_type == "horizontal":
                # Two converging lines (horizontal dart)
                draw_guide_line(c, dart_x, dart_y + 3*mm, dart_x + 35*mm, dart_y)
                draw_guide_line(c, dart_x, dart_y - 3*mm, dart_x + 35*mm, dart_y)

            elif dart_type == "vertical":
                # Vertical converging dart
                draw_guide_line(c, dart_x - 5*mm, dart_y + 5*mm, dart_x, dart_y - 25*mm)
                draw_guide_line(c, dart_x + 5*mm, dart_y + 5*mm, dart_x, dart_y - 25*mm)

            elif dart_type == "diagonal":
                # Diagonal dart
                draw_guide_line(c, dart_x, dart_y + 3*mm, dart_x + 30*mm, dart_y - 20*mm)
                draw_guide_line(c, dart_x + 6*mm, dart_y + 3*mm, dart_x + 30*mm, dart_y - 20*mm)

            else:  # curved
                # Curved dart
                path = c.beginPath()
                path.moveTo(dart_x, dart_y + 3*mm)
                path.curveTo(dart_x + 15*mm, dart_y, dart_x + 25*mm, dart_y - 10*mm, dart_x + 30*mm, dart_y - 15*mm)
                c.drawPath(path, fill=0, stroke=1)

                path = c.beginPath()
                path.moveTo(dart_x + 5*mm, dart_y + 3*mm)
                path.curveTo(dart_x + 18*mm, dart_y + 2*mm, dart_x + 27*mm, dart_y - 8*mm, dart_x + 30*mm, dart_y - 15*mm)
                c.drawPath(path, fill=0, stroke=1)

    c.setDash()
    draw_footer(c)

def main():
    output_path = os.path.join(os.path.dirname(__file__), "CuttingMaster-Stitching-Practice-Guide.pdf")

    c = canvas.Canvas(output_path, pagesize=A4)
    c.setTitle("CuttingMaster Stitching Practice Guide")
    c.setAuthor("CuttingMaster")
    c.setSubject("Stitching Practice Worksheets for Tailors")

    # Generate all pages
    pages = [
        page_cover,
        page_horizontal_lines,
        page_vertical_lines,
        page_diagonal_lines,
        page_seam_allowance,
        page_waves,
        page_s_curves,
        page_corners,
        page_circles,
        page_necklines,
        page_armholes,
        page_darts
    ]

    for i, page_func in enumerate(pages):
        page_func(c)
        if i < len(pages) - 1:
            c.showPage()

    c.save()
    print(f"PDF generated: {output_path}")
    return output_path

if __name__ == "__main__":
    main()
