from pathlib import Path
from zipfile import ZipFile
import xml.etree.ElementTree as ET

SOURCE = Path('/Users/mymac/Downloads/MONITORING SIPRIMA - ELECTRIC SOLUTION UP 3.xlsx')
NS = {'x': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main', 'r': 'http://schemas.openxmlformats.org/officeDocument/2006/relationships'}

def column(cell_ref: str) -> str:
    return ''.join(char for char in cell_ref if char.isalpha())

with ZipFile(SOURCE) as book:
    shared = []
    if 'xl/sharedStrings.xml' in book.namelist():
        root = ET.fromstring(book.read('xl/sharedStrings.xml'))
        for item in root.findall('x:si', NS):
            shared.append(''.join(item.itertext()))

    rels = ET.fromstring(book.read('xl/_rels/workbook.xml.rels'))
    targets = {rel.attrib['Id']: rel.attrib['Target'] for rel in rels}
    workbook = ET.fromstring(book.read('xl/workbook.xml'))

    target_names = {'Recap Penawaran Esol', 'Realisasi E. Solution', 'Recap E-Solution 2026'}
    for sheet in workbook.findall('x:sheets/x:sheet', NS):
        name = sheet.attrib['name']
        if name not in target_names:
            continue
        rel_id = sheet.attrib['{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id']
        target = targets[rel_id]
        path = 'xl/' + target.lstrip('/')
        root = ET.fromstring(book.read(path))
        dimension = root.find('x:dimension', NS)
        print(f'=== SHEET: {name} | USED: {dimension.attrib.get("ref", "unknown") if dimension is not None else "unknown"} ===')
        rows = root.findall('x:sheetData/x:row', NS)
        for row in rows[:8]:
            values = []
            for cell in row.findall('x:c', NS):
                ref = cell.attrib.get('r', '')
                raw = cell.find('x:v', NS)
                formula = cell.find('x:f', NS)
                value = raw.text if raw is not None else ''
                if cell.attrib.get('t') == 's' and value:
                    value = shared[int(value)]
                elif cell.attrib.get('t') == 'inlineStr':
                    inline = cell.find('x:is', NS)
                    value = ''.join(inline.itertext()) if inline is not None else ''
                if formula is not None:
                    value = f'FORMULA={formula.text} | cached={value}'
                values.append(f'{column(ref)}:{value}')
            print(' | '.join(values))
        print()
