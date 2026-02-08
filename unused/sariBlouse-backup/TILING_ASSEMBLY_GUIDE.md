# Pattern Tiling & Assembly Guide

## Overview

When pattern pieces are larger than the selected paper size (A4, A3, Letter, etc.), the PDF generator automatically **tiles** them across multiple pages. Each page contains registration marks and diagonal alignment lines to help you perfectly align and assemble the tiles into a complete pattern.

---

## Match Markers Implemented

### 1. **Corner Registration Marks (Crosshairs)**

**What they look like:**
```
    ⊕
```
A small circle with horizontal and vertical crosshairs at each corner.

**Where they appear:**
- Top-left corner: All tiles
- Top-right corner: All tiles except last column
- Bottom-left corner: All tiles except last row
- Bottom-right corner: All tiles except last tile

**How to use them:**
1. Print all tiles at **100% scale** (NO scaling, NO "fit to page")
2. Place two adjacent tiles with ~0.5" overlap
3. Align the crosshair marks so they overlap perfectly
4. When aligned, the circles and crosshairs should form a single, complete symbol
5. Tape the tiles together from the back

**Tolerance:** Aim for ±1mm alignment accuracy

---

### 2. **Diagonal Alignment Lines**

**What they look like:**
- Thin, dashed, gray diagonal lines in overlap zones
- 3 lines per edge (evenly spaced)
- Angle: ~45 degrees from horizontal

**Where they appear:**
- Right edge: If tile is not in the last column
- Bottom edge: If tile is not in the last row

**How to use them:**
1. After aligning registration marks, check diagonal lines
2. The diagonal lines from adjacent tiles should continue seamlessly
3. If lines don't match, adjust tile position slightly
4. Perfect alignment = continuous diagonal pattern across both tiles

**Purpose:** Visual confirmation that tiles are perfectly aligned (not skewed or rotated)

---

## Step-by-Step Assembly Instructions

### **Materials Needed:**
- Printed pattern tiles (100% scale, no scaling)
- Clear tape or glue stick
- Ruler (to verify 1" scale check line on each page)
- Flat, clean work surface
- Scissors or rotary cutter

### **Assembly Process:**

#### **Step 1: Verify Print Scale**
✅ Each page has a **1" scale verification line** at the bottom
✅ Measure this line with a ruler - it MUST be exactly 1 inch
✅ If not, reprint at 100% scale (disable "fit to page")

#### **Step 2: Identify Tile Layout**
✅ Each page shows "Tile X of Y" in the top-right corner
✅ Tiles are numbered left-to-right, top-to-bottom:
```
┌───┬───┬───┐
│ 1 │ 2 │ 3 │  ← Row 1
├───┼───┼───┤
│ 4 │ 5 │ 6 │  ← Row 2
└───┴───┴───┘
```

#### **Step 3: Assemble Row by Row**

**For each row:**

1. **Start with leftmost tile** (e.g., Tile 1)

2. **Add next tile to the right** (e.g., Tile 2):
   - Overlap tiles by ~0.5" at the right edge
   - Align the **top-right registration mark** of Tile 1 with **top-left registration mark** of Tile 2
   - Align the **bottom-right registration mark** of Tile 1 with **bottom-left registration mark** of Tile 2
   - Check that **diagonal lines continue seamlessly** across both tiles

3. **Tape from the back**:
   - Flip the assembled tiles over
   - Use clear tape along the overlap zone
   - Tape on the BACK side only (keeps front clean)

4. **Repeat** for remaining tiles in the row

#### **Step 4: Assemble Rows Together**

1. **Place Row 1 on top of Row 2** with ~0.5" overlap

2. **Align registration marks**:
   - Bottom-left mark of Row 1 → Top-left mark of Row 2
   - Bottom-right mark of Row 1 → Top-right mark of Row 2

3. **Check diagonal lines** across the bottom edge of Row 1 and top edge of Row 2

4. **Tape from the back**

5. **Repeat** for remaining rows

#### **Step 5: Final Trimming**

1. **Identify outer cutting line**:
   - Should be a solid black line around the pattern perimeter
   - May have label "CUT HERE AFTER ASSEMBLY"

2. **Trim assembled pattern**:
   - Cut along the outer edge (outside the registration marks)
   - Remove overlap zones if marked

3. **Your pattern is ready!**

---

## Troubleshooting

### **Problem: Registration marks don't align**

**Possible causes:**
- ❌ Printer scaled the pages (not 100%)
- ❌ Different tiles printed with different scaling
- ❌ Paper warped or curled

**Solutions:**
- ✅ Verify 1" scale check on each page
- ✅ Reprint at exactly 100% scale
- ✅ Use printer setting: "Actual Size" or "100%" or "No Scaling"
- ✅ Flatten pages under heavy books before assembly

---

### **Problem: Diagonal lines don't continue across tiles**

**Possible causes:**
- ❌ Tiles are skewed or rotated
- ❌ Overlap zone is too wide/narrow

**Solutions:**
- ✅ Rotate tile slightly until diagonal lines align
- ✅ Adjust overlap distance
- ✅ Re-check registration marks

---

### **Problem: Pattern pieces don't fit together after assembly**

**Possible causes:**
- ❌ Tiles assembled in wrong order
- ❌ Scale verification failed

**Solutions:**
- ✅ Double-check tile numbers (Tile 1 of 6, etc.)
- ✅ Refer to assembly diagram (if provided on first page)
- ✅ Re-verify scale on each page

---

## Tips for Best Results

✅ **Use a flat, clean surface** - Glass or laminate table works best

✅ **Work in good lighting** - Registration marks are small and need to be seen clearly

✅ **Use a magnifying glass** - For precise alignment of crosshairs

✅ **Tape from the back only** - Keeps pattern front clean for cutting

✅ **Check scale before assembly** - Measure the 1" verification line on EVERY page

✅ **Don't rush** - Take your time aligning each tile perfectly

✅ **Use a rotary cutter** - For final trimming (safer and more accurate than scissors)

---

## Technical Details

### **Overlap Zone:**
- Default: 0.5 inches
- Contains registration marks and diagonal alignment lines
- Should be trimmed away after assembly (if marked)

### **Registration Mark Specs:**
- Circle radius: 0.08"
- Crosshair length: 0.25"
- Line thickness: 0.012"
- Color: Black (100%)

### **Diagonal Line Specs:**
- Line thickness: 0.008"
- Pattern: Dashed (1pt line, 2pt gap)
- Color: Medium gray (50%)
- Angle: ~45 degrees
- Count: 3 per overlap zone

### **Tile Margin:**
- Paper margin: 0.3" (all sides)
- Additional space: 1.0" at top for title/watermark

---

## Example Assembly Diagrams

### **2x2 Grid (4 tiles)**
```
STEP 1: Assemble Row 1
┌───────┬───────┐
│ Tile1 │ Tile2 │
│   ⊕───┼───⊕   │ ← Align these marks
└───────┴───────┘

STEP 2: Assemble Row 2
┌───────┬───────┐
│ Tile3 │ Tile4 │
│   ⊕───┼───⊕   │ ← Align these marks
└───────┴───────┘

STEP 3: Join Rows
┌───────┬───────┐
│ Tile1 │ Tile2 │
│   ⊕───┼───⊕   │
├───⊕───┼───⊕───┤ ← Align these marks
│ Tile3 │ Tile4 │
│   ⊕───┼───⊕   │
└───────┴───────┘
```

### **3x2 Grid (6 tiles)**
```
Row 1: Tile1 → Tile2 → Tile3
Row 2: Tile4 → Tile5 → Tile6

Assembly:
┌───┬───┬───┐
│ 1 │ 2 │ 3 │  Align → marks horizontally
├───┼───┼───┤
│ 4 │ 5 │ 6 │  Align ↓ marks vertically
└───┴───┴───┘
```

---

## FAQ

**Q: Can I print tiles on different days?**
A: Yes, but ensure the SAME printer and SAME settings. Different printers may have slight scale variations.

**Q: Can I use A4 for some tiles and Letter for others?**
A: No. All tiles must use the same paper size. The system automatically calculates tiles for one paper size.

**Q: What if my printer doesn't support borderless printing?**
A: That's okay. The 0.3" margin ensures content doesn't get cut off even with printer margins.

**Q: Can I laminate tiles before assembly?**
A: Not recommended. Laminate after assembly to avoid thickness issues at overlap zones.

**Q: Do I need to keep the registration marks on the final pattern?**
A: No. After assembly, cut along the outer edge. Registration marks should be trimmed away.

---

## Support

For issues or questions about pattern assembly, please contact CuttingMaster support or refer to the video assembly tutorial at: [Link to be added]

---

**Document Version:** 1.0
**Last Updated:** January 17, 2026
**Author:** CuttingMaster Team
