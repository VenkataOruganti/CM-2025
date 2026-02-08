# Backup Changelog

This file documents all backups and the changes made between versions.

---

## Current Backup Strategy (Updated Jan 17, 2026)

**Philosophy:** Keep only the latest STABLE version of each file type.
- ✅ Single source of truth
- ✅ No duplicate/intermediate versions
- ✅ Maximum space efficiency
- ✅ Easy to understand and restore

**Current Backups:**
- `sariBlouse_BACKUP_20260117_STABLE.php` (198K)
- `sariBlouse_PDF_BACKUP_20260117_STABLE.php` (56K)
- `sariBlouse_SVG_BACKUP_20260117_STABLE.php` (9.3K)
- `BACKUP_CHANGELOG.md` (5.4K)

**Total:** 4 files, ~276 KB

---

## 2026-01-17 - STABLE Version (CURRENT)

**Backup Files:**
- `sariBlouse_BACKUP_20260117_STABLE.php` (198 KB)
- `sariBlouse_PDF_BACKUP_20260117_STABLE.php` (56 KB)
- `sariBlouse_SVG_BACKUP_20260117_STABLE.php` (9.3 KB)

**All Features Included:**

### Main File (sariBlouse.php)
- Complete blouse pattern generation (front, back, patti, sleeve)
- Waist curve adjustments
- S-curve sleeve cap calculations
- Measurement-driven parametric design
- SVG output with proper viewBox
- Session storage for pattern data
- All 14 saree blouse measurement fields

### PDF File (sariBlouse_PDF.php)
- Multi-page tiling support (automatic)
- **Registration marks (⊕)** - Corner crosshairs for tile alignment
- **Diagonal alignment lines** - Visual verification of proper alignment
- Paper size support (A2, A3, A4, Letter, Legal, Tabloid)
- Portrait/Landscape auto-selection
- Scale verification box
- CM logo and watermark
- **Fixed margin calculation** - Removed incorrect 1.0" deduction
- Tile reference labels
- Pattern offset corrections

### SVG File (sariBlouse_SVG.php)
- Standalone SVG export
- Proper scaling and viewBox
- Clean SVG output for web display

**Key Improvements vs Previous Versions:**
1. ✅ **Registration marks on ALL 4 corners** of every tile (bug fix)
2. ✅ **Diagonal alignment lines** for visual confirmation
3. ✅ **Fixed margin gap** - Removed 0.7-1.0" gap between pattern and marks
4. ✅ **1.0" more usable height** per page (better paper utilization)
5. ✅ **Waist curve adjustments** for better fit
6. ✅ **S-curve sleeve improvements** for professional shaping

**Bug Fixes:**
- Fixed registration mark placement logic (all corners, all tiles)
- Fixed usable height calculation (removed bogus 1.0" title deduction)
- Fixed pattern-to-mark alignment (no more gaps)

---

## Previous Versions (Removed)

### 2026-01-17 - WAISTCURVE Version
**Status:** ❌ Consolidated into STABLE (Jan 17)
**Reason:** Same content as new STABLE version

### 2026-01-17 - TILING_MARKS Version
**Status:** ❌ Consolidated into STABLE (Jan 17)
**Reason:** PDF changes now included in new STABLE version

### 2026-01-17 - SCURVE Version
**Status:** ❌ Removed - Intermediate version
**Reason:** Superseded by WAISTCURVE, then consolidated into STABLE

### 2026-01-16 - STABLE Version (Original)
**Status:** ❌ Replaced with Jan 17 STABLE
**Reason:** Outdated - lacked tiling registration marks and bug fixes

---

## Backup Naming Convention

Format: `<filename>_BACKUP_YYYYMMDD_STABLE.php`

Where:
- `YYYYMMDD` = Date of backup (e.g., 20260117 = January 17, 2026)
- `STABLE` = Indicates this is the current stable version (only one per date)

---

## How to Restore a Backup

To restore from backup:

```bash
# Navigate to pattern directory
cd /Users/venkataoruganti/Desktop/CM-2025/patterns/saree_blouses/sariBlouse

# Restore main file
cp backup/sariBlouse_BACKUP_20260117_STABLE.php sariBlouse.php

# Restore PDF file
cp backup/sariBlouse_PDF_BACKUP_20260117_STABLE.php sariBlouse_PDF.php

# Restore SVG file
cp backup/sariBlouse_SVG_BACKUP_20260117_STABLE.php sariBlouse_SVG.php

# Verify restoration
php -l sariBlouse.php
php -l sariBlouse_PDF.php
php -l sariBlouse_SVG.php
```

**Warning:** Always test backups in a development environment before deploying to production!

---

## Backup Inventory

| File Type | Date | Size | Features |
|-----------|------|------|----------|
| **Main** | Jan 17 | 198K | All pattern generation + waist curves + S-curves |
| **PDF** | Jan 17 | 56K | Tiling + registration marks + diagonal lines + bug fixes |
| **SVG** | Jan 17 | 9.3K | Standalone SVG export |
| **Changelog** | Jan 17 | 5.4K | This documentation |

**Total Size:** 276 KB (67% reduction from 835 KB initial size)

---

## Retention Policy

✅ **Keep:** Latest STABLE version of each file type (main/PDF/SVG)
❌ **Remove:** All intermediate versions (SCURVE, WAISTCURVE, TILING_MARKS, etc.)
❌ **Remove:** Older STABLE versions when new STABLE is created
✅ **Document:** All removed versions in this changelog for reference

**Update Frequency:**
- Create new STABLE backup when significant features are added
- Remove old STABLE and all intermediate versions
- Keep backup folder clean and minimal

---

## Version History Summary

| Version | Date | Status | Notes |
|---------|------|--------|-------|
| STABLE | Jan 17, 2026 | ✅ Current | All features + bug fixes |
| TILING_MARKS | Jan 17, 2026 | ❌ Consolidated | Merged into STABLE |
| WAISTCURVE | Jan 17, 2026 | ❌ Consolidated | Merged into STABLE |
| SCURVE | Jan 17, 2026 | ❌ Removed | Intermediate version |
| STABLE | Jan 16, 2026 | ❌ Replaced | Original baseline |

---

**Last Updated:** January 17, 2026 21:26
**Maintained By:** CuttingMaster Team
**Backup Strategy:** Single STABLE version per file type
