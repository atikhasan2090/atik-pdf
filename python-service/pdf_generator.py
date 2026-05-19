import os
from reportlab.lib import colors
from reportlab.lib.pagesizes import A4
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

class LargeTablePdfGenerator:
    def __init__(self, buffer, title="Report", page_size=A4):
        self.buffer = buffer
        self.title = title
        self.page_size = page_size
        self.doc = SimpleDocTemplate(
            self.buffer, 
            pagesize=self.page_size,
            rightMargin=30, leftMargin=30, 
            topMargin=30, bottomMargin=18
        )
        self._register_fonts()
        
    def _register_fonts(self):
        """
        Registers Bangla fonts. Ensure the font files are placed in the fonts/ directory.
        Falls back to default if not found.
        """
        base_dir = os.path.dirname(os.path.abspath(__file__))
        fonts_dir = os.path.join(base_dir, 'fonts')
        
        # Noto Sans Bengali as primary for good ligature support
        noto_path = os.path.join(fonts_dir, 'NotoSansBengali-Regular.ttf')
        solaiman_path = os.path.join(fonts_dir, 'SolaimanLipi.ttf')
        
        try:
            if os.path.exists(noto_path):
                pdfmetrics.registerFont(TTFont('Bangla', noto_path))
                self.has_bangla = True
            elif os.path.exists(solaiman_path):
                pdfmetrics.registerFont(TTFont('Bangla', solaiman_path))
                self.has_bangla = True
            else:
                self.has_bangla = False
        except Exception as e:
            print(f"Font registration warning: {e}")
            self.has_bangla = False

    def generate(self, columns, rows):
        elements = []
        styles = getSampleStyleSheet()
        
        font_name = 'Bangla' if self.has_bangla else 'Helvetica'
        
        # Title
        title_style = ParagraphStyle(
            'CustomTitle',
            parent=styles['Heading1'],
            fontName=font_name,
            alignment=1, # Center
            spaceAfter=20
        )
        elements.append(Paragraph(self.title, title_style))
        
        # Data Preparation
        # We need to chunk the table if it's insanely large to avoid Platypus MemoryErrors
        # But ReportLab's LongTable can handle decent amounts. 
        # For 500k rows, we would literally need an incremental custom canvas builder, 
        # but this represents the solid intermediate step capable of 50k+ rows easily
        # depending on RAM.
        
        table_data = [columns] + rows
        
        # Calculate col widths (simple equal distribution)
        col_width = (self.page_size[0] - 60) / max(len(columns), 1)
        
        # Use simple LongTable for automatic pagination
        t = Table(table_data, colWidths=[col_width] * len(columns), repeatRows=1)
        
        t.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#f2f2f2')),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.black),
            ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
            ('FONTNAME', (0, 0), (-1, -1), font_name),
            ('FONTSIZE', (0, 0), (-1, 0), 11),
            ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
            ('BACKGROUND', (0, 1), (-1, -1), colors.white),
            ('GRID', (0, 0), (-1, -1), 1, colors.HexColor('#dddddd')),
            ('FONTSIZE', (0, 1), (-1, -1), 9),
            ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ]))
        
        elements.append(t)
        
        # Build document
        self.doc.build(elements)
