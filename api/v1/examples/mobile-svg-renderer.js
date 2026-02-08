/**
 * =============================================================================
 * MOBILE SVG RENDERER
 * =============================================================================
 *
 * JavaScript/TypeScript module for reconstructing SVG patterns from
 * nodes and paths data received from the API.
 *
 * This can be used in:
 * - React Native (with react-native-svg)
 * - Flutter (via webview or dart conversion)
 * - Ionic/Cordova
 * - Progressive Web App
 *
 * USAGE:
 *   import { PatternRenderer, generatePDF } from './mobile-svg-renderer';
 *
 *   // Fetch pattern data from API
 *   const response = await fetch('/api/v1/pattern/nodes.php?measurement_id=97');
 *   const { data } = await response.json();
 *
 *   // Render SVG for display
 *   const svg = PatternRenderer.renderPattern(data.patterns.front);
 *
 *   // Generate PDF
 *   const pdfBytes = await generatePDF(data);
 */

// =============================================================================
// PATTERN RENDERER CLASS
// =============================================================================

class PatternRenderer {
    /**
     * Render a single pattern to SVG string
     * @param {Object} pattern - Pattern data with nodes, paths, labels
     * @param {Object} options - Rendering options
     * @returns {string} SVG string
     */
    static renderPattern(pattern, options = {}) {
        const {
            showNodes = false,       // Show node markers (for debugging)
            showLabels = true,       // Show text labels
            strokeWidth = 1.5,       // Main line stroke width
            scale = 1.0              // Scale factor for rendering
        } = options;

        const viewBox = pattern.viewBox || '0 0 500 600';
        const [, , width, height] = viewBox.split(' ').map(Number);

        let svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}" width="${width * scale}" height="${height * scale}">`;

        // Background
        svg += `<rect width="100%" height="100%" fill="white"/>`;

        // Render paths
        svg += this.renderPaths(pattern.paths, strokeWidth);

        // Render labels
        if (showLabels && pattern.labels) {
            svg += this.renderLabels(pattern.labels);
        }

        // Render nodes (for debugging)
        if (showNodes && pattern.nodes) {
            svg += this.renderNodes(pattern.nodes);
        }

        svg += '</svg>';
        return svg;
    }

    /**
     * Render SVG paths
     */
    static renderPaths(paths, strokeWidth = 1.5) {
        if (!paths) return '';

        let svg = '';

        for (const [key, pathData] of Object.entries(paths)) {
            // Determine stroke color and style based on path type
            let stroke = '#000000';
            let dashArray = 'none';
            let opacity = 1;

            if (key.includes('cutting') || key.includes('red')) {
                stroke = '#DC2626';
                dashArray = '6,3';
                strokeWidth = 0.5;
            } else if (key.includes('fold') || key.includes('gray')) {
                stroke = '#808080';
                dashArray = '6,4';
                strokeWidth = 0.5;
            } else if (key.includes('dashed')) {
                dashArray = '4,2';
            }

            svg += `<path d="${pathData}" fill="none" stroke="${stroke}" stroke-width="${strokeWidth}" stroke-dasharray="${dashArray}" stroke-linecap="round" stroke-linejoin="round"/>`;
        }

        return svg;
    }

    /**
     * Render text labels
     */
    static renderLabels(labels) {
        if (!labels || !labels.length) return '';

        let svg = '';

        for (const label of labels) {
            const transform = label.rotation
                ? `transform="rotate(${label.rotation}, ${label.x}, ${label.y})"`
                : '';

            svg += `<text x="${label.x}" y="${label.y}" font-size="10" fill="#666" text-anchor="middle" ${transform}>${this.escapeXml(label.text)}</text>`;
        }

        return svg;
    }

    /**
     * Render node markers (for debugging)
     */
    static renderNodes(nodes) {
        if (!nodes) return '';

        let svg = '<g class="debug-nodes">';

        for (const [key, node] of Object.entries(nodes)) {
            // Node marker
            svg += `<circle cx="${node.x}" cy="${node.y}" r="3" fill="#3B82F6" stroke="#1D4ED8" stroke-width="1"/>`;

            // Node label
            svg += `<text x="${node.x + 5}" y="${node.y - 5}" font-size="8" fill="#3B82F6">${key}</text>`;
        }

        svg += '</g>';
        return svg;
    }

    /**
     * Escape XML special characters
     */
    static escapeXml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&apos;');
    }
}

// =============================================================================
// SVG TO PDF CONVERSION (for mobile)
// =============================================================================

/**
 * Generate PDF from pattern data
 * Uses jsPDF or similar library
 *
 * @param {Object} patternData - Full pattern data from API
 * @param {Object} options - PDF options
 * @returns {Promise<Uint8Array>} PDF bytes
 */
async function generatePDF(patternData, options = {}) {
    const {
        paperSize = 'A3',
        orientation = 'portrait',
        margin = 1.0,  // inches
        scale = 25.4   // pixels per inch
    } = options;

    // Paper dimensions in inches
    const paperSizes = {
        'A4': { width: 8.27, height: 11.69 },
        'A3': { width: 11.69, height: 16.54 },
        'LETTER': { width: 8.5, height: 11.0 },
        'LEGAL': { width: 8.5, height: 14.0 }
    };

    const paper = paperSizes[paperSize] || paperSizes['A3'];

    // For React Native, use react-native-pdf-lib
    // For web, use jsPDF
    // This is a placeholder showing the structure

    const pdf = {
        pages: [],
        metadata: {
            title: `${patternData.metadata.customer_name} - Blouse Pattern`,
            author: 'CuttingMaster.in',
            createdAt: new Date().toISOString()
        }
    };

    // Add summary page
    pdf.pages.push({
        type: 'summary',
        content: {
            customerName: patternData.metadata.customer_name,
            measurements: patternData.measurements,
            patternCount: Object.keys(patternData.patterns).length
        }
    });

    // Add each pattern page
    for (const [key, pattern] of Object.entries(patternData.patterns)) {
        const svg = PatternRenderer.renderPattern(pattern);

        pdf.pages.push({
            type: 'pattern',
            name: pattern.name,
            svg: svg,
            viewBox: pattern.viewBox
        });
    }

    // In actual implementation, use PDF library to generate bytes
    // return pdfLibrary.generateBytes(pdf);

    console.log('PDF structure generated:', pdf);
    return pdf;
}

// =============================================================================
// API CLIENT
// =============================================================================

class PatternAPIClient {
    constructor(baseUrl = '') {
        this.baseUrl = baseUrl;
    }

    /**
     * Fetch pattern nodes from API
     * @param {number} measurementId - Measurement ID
     * @param {string[]} patterns - Which patterns to include
     * @returns {Promise<Object>} Pattern data
     */
    async getPatternNodes(measurementId, patterns = ['front', 'back', 'sleeve']) {
        const url = `${this.baseUrl}/api/v1/pattern/nodes.php?measurement_id=${measurementId}&patterns=${patterns.join(',')}`;

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                // 'Authorization': 'Bearer YOUR_TOKEN'  // Add auth when implemented
            }
        });

        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error?.message || 'Unknown error');
        }

        return result.data;
    }

    /**
     * Generate and download PDF locally
     * @param {number} measurementId - Measurement ID
     * @param {Object} options - PDF options
     */
    async generateLocalPDF(measurementId, options = {}) {
        // 1. Fetch nodes (minimal data transfer)
        const patternData = await this.getPatternNodes(measurementId);

        // 2. Generate PDF locally
        const pdf = await generatePDF(patternData, options);

        return pdf;
    }
}

// =============================================================================
// REACT NATIVE EXAMPLE USAGE
// =============================================================================

/*
// In React Native component:

import React, { useState, useEffect } from 'react';
import { View, Button } from 'react-native';
import { SvgXml } from 'react-native-svg';
import RNHTMLtoPDF from 'react-native-html-to-pdf';

const PatternViewer = ({ measurementId }) => {
    const [patternData, setPatternData] = useState(null);
    const [currentPattern, setCurrentPattern] = useState('front');

    useEffect(() => {
        const loadPattern = async () => {
            const client = new PatternAPIClient('https://cuttingmaster.in');
            const data = await client.getPatternNodes(measurementId);
            setPatternData(data);
        };
        loadPattern();
    }, [measurementId]);

    const renderSVG = () => {
        if (!patternData) return null;
        const pattern = patternData.patterns[currentPattern];
        const svg = PatternRenderer.renderPattern(pattern);
        return <SvgXml xml={svg} width="100%" height="100%" />;
    };

    const generatePDF = async () => {
        if (!patternData) return;

        // Build HTML with all pattern SVGs
        let html = `
            <html>
            <head>
                <style>
                    @page { size: A3 portrait; margin: 1in; }
                    .pattern { page-break-after: always; }
                    svg { width: 100%; height: auto; }
                </style>
            </head>
            <body>
        `;

        for (const [key, pattern] of Object.entries(patternData.patterns)) {
            const svg = PatternRenderer.renderPattern(pattern);
            html += `<div class="pattern"><h2>${pattern.name}</h2>${svg}</div>`;
        }

        html += '</body></html>';

        // Generate PDF using react-native-html-to-pdf
        const pdf = await RNHTMLtoPDF.convert({
            html: html,
            fileName: `${patternData.metadata.customer_name}_pattern`,
            directory: 'Documents',
            width: 842,  // A3 width in points
            height: 1191 // A3 height in points
        });

        console.log('PDF saved to:', pdf.filePath);
        return pdf.filePath;
    };

    return (
        <View style={{ flex: 1 }}>
            <View style={{ flexDirection: 'row', padding: 10 }}>
                <Button title="Front" onPress={() => setCurrentPattern('front')} />
                <Button title="Back" onPress={() => setCurrentPattern('back')} />
                <Button title="Sleeve" onPress={() => setCurrentPattern('sleeve')} />
            </View>

            <View style={{ flex: 1 }}>
                {renderSVG()}
            </View>

            <Button title="Generate PDF" onPress={generatePDF} />
        </View>
    );
};

export default PatternViewer;
*/

// =============================================================================
// FLUTTER EXAMPLE (Dart-like pseudocode)
// =============================================================================

/*
// In Flutter:

import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

class PatternViewer extends StatefulWidget {
    final int measurementId;
    PatternViewer({required this.measurementId});

    @override
    _PatternViewerState createState() => _PatternViewerState();
}

class _PatternViewerState extends State<PatternViewer> {
    Map<String, dynamic>? patternData;
    String currentPattern = 'front';

    @override
    void initState() {
        super.initState();
        loadPattern();
    }

    Future<void> loadPattern() async {
        final response = await http.get(Uri.parse(
            'https://cuttingmaster.in/api/v1/pattern/nodes.php?measurement_id=${widget.measurementId}'
        ));

        if (response.statusCode == 200) {
            final data = json.decode(response.body);
            setState(() {
                patternData = data['data'];
            });
        }
    }

    Future<void> generatePDF() async {
        if (patternData == null) return;

        final pdf = pw.Document();

        // Add each pattern as a page
        for (final entry in patternData!['patterns'].entries) {
            final pattern = entry.value;

            pdf.addPage(
                pw.Page(
                    pageFormat: PdfPageFormat.a3,
                    build: (context) {
                        return pw.Column(
                            children: [
                                pw.Text(pattern['name'], style: pw.TextStyle(fontSize: 24)),
                                pw.SvgImage(svg: buildSvgString(pattern)),
                            ],
                        );
                    },
                ),
            );
        }

        // Save or share PDF
        await Printing.sharePdf(bytes: await pdf.save(), filename: 'pattern.pdf');
    }

    @override
    Widget build(BuildContext context) {
        return Scaffold(
            appBar: AppBar(title: Text('Pattern Viewer')),
            body: Column(
                children: [
                    // Pattern selector
                    Row(
                        children: ['front', 'back', 'sleeve'].map((p) =>
                            ElevatedButton(
                                onPressed: () => setState(() => currentPattern = p),
                                child: Text(p.toUpperCase()),
                            )
                        ).toList(),
                    ),

                    // SVG display
                    Expanded(
                        child: patternData != null
                            ? SvgPicture.string(buildSvgString(patternData!['patterns'][currentPattern]))
                            : CircularProgressIndicator(),
                    ),

                    // PDF button
                    ElevatedButton(
                        onPressed: generatePDF,
                        child: Text('Generate PDF'),
                    ),
                ],
            ),
        );
    }
}
*/

// =============================================================================
// EXPORTS
// =============================================================================

// For ES6 modules
// export { PatternRenderer, PatternAPIClient, generatePDF };

// For CommonJS
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { PatternRenderer, PatternAPIClient, generatePDF };
}

// For browser global
if (typeof window !== 'undefined') {
    window.PatternRenderer = PatternRenderer;
    window.PatternAPIClient = PatternAPIClient;
    window.generatePDF = generatePDF;
}
