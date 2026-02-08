# Saree Blouses - Folder Structure

## 📁 Directory Organization

```
/patterns/saree_blouses/
├── README.md                          # Main overview
├── REORGANIZATION_SUMMARY.txt         # Historical reorganization notes
├── FOLDER_STRUCTURE.md               # This file
│
└── sariBlouse/                        # ⭐ Saree Blouse Pattern Generator
    │
    ├── 📄 Main Files
    │   ├── sariBlouse.php                      # Current production version
    │   ├── sariBlouse_old.php                     # Legacy version (v1)
    │   ├── sariBlouse_paper_config.php         # Paper size selection UI
    │   ├── sariBlouse_pdf.php                  # PDF export with tiling
    │   └── sariBlouse_svg.php                  # SVG export with ZIP
    │
    ├── 💾 Backups
    │   └── sariBlouse_v2_CONSOLIDATED_BACKUP_20260115_081427_NO_DELETE.php
    │
    └── 📚 Documentation
        ├── DOCUMENTATION.md                      # Complete documentation (consolidated)
        └── BACKUP_POLICY.md                      # Backup guidelines
```

## 🎯 File Purposes

### Main Pattern Generator
**sariBlouse.php** - Complete pattern generation system
- Section 1: Configuration & Data Loading
- Section 2: Business Logic (all calculations)
- Section 3: Presentation Layer (HTML/SVG rendering)
- Features: 115+ nodes, 47 snips, session storage

### Export Systems

**sariBlouse_paper_config.php** - Beautiful UI for paper selection
- 5 paper sizes (A4, A3, Letter, Legal, Tabloid)
- Visual cards with regions
- Links to PDF and SVG generators

**sariBlouse_pdf.php** - Professional PDF export
- Printer-independent
- Automatic tiling for large patterns
- Smart orientation (Portrait/Landscape)
- Landscape rotation for tiles

**sariBlouse_svg.php** - Vector graphics export
- 4 separate SVG files
- ZIP packaging
- Includes README with measurements
- Universal compatibility

## 🚀 Usage

### Access Pattern Generator
```
URL: /patterns/saree_blouses/sariBlouse/sariBlouse.php?measurement_id=123
```

### Export Options
```
PDF: /patterns/saree_blouses/sariBlouse/sariBlouse_paper_config.php?measurement_id=123
SVG: /patterns/saree_blouses/sariBlouse/sariBlouse_svg.php?measurement_id=123
```

## 📊 Key Features

### Pattern Generation
✅ 4 patterns (Front, Back, Patti, Sleeve)
✅ 14 measurements from database
✅ 115+ calculated nodes
✅ 47 snip markers
✅ 2"×2" scale verification box
✅ Print & Dev modes

### PDF Export
✅ 5 paper size options
✅ Auto-tiling (landscape)
✅ Smart orientation
✅ Professional layout

### SVG Export
✅ 4 vector files
✅ ZIP archive
✅ Infinite scalability
✅ Software compatible

## 📝 Documentation Guide

**Main Documentation:**
- [DOCUMENTATION.md](sariBlouse/DOCUMENTATION.md) - Complete consolidated documentation
  - Quick Start Guide
  - Overview & Features
  - Pattern Data Structure
  - Database Measurements
  - PDF & SVG Export
  - Snip Icons System
  - Implementation Details
  - Testing & Verification
  - API Reference
  - Troubleshooting

**Backup Information:**
- [BACKUP_POLICY.md](sariBlouse/BACKUP_POLICY.md) - Backup policy and recovery procedures

## 🔄 Version History

### v2.0 (Current) - January 15, 2026
- Complete data/logic/presentation separation
- Session storage implementation
- PDF/SVG export with tiling
- Paper size configuration UI
- Comprehensive documentation

### v1.0 - January 14, 2026
- Initial pattern generation
- Basic 4 patterns
- Dev/Print modes

## 📦 Future Patterns

This folder structure allows for easy addition of new pattern types:

```
/patterns/saree_blouses/
├── sariBlouse/         # Saree blouse patterns
├── churidarTop/        # Future: Churidar top patterns
├── kurti/              # Future: Kurti patterns
└── ...                 # Future: Other patterns
```

Each pattern type gets its own subfolder with:
- Main generator file
- Export generators
- Documentation
- Backups

---

**Organization Date:** January 15, 2026
**Structure Version:** 1.0
