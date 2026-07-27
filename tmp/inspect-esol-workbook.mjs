import { FileBlob, SpreadsheetFile } from '@oai/artifact-tool';

const input = await FileBlob.load('/Users/mymac/Downloads/MONITORING SIPRIMA - ELECTRIC SOLUTION UP 3.xlsx');
const workbook = await SpreadsheetFile.importXlsx(input);

const overview = await workbook.inspect({
  kind: 'workbook,sheet,table',
  maxChars: 12000,
  tableMaxRows: 8,
  tableMaxCols: 12,
  tableMaxCellChars: 100,
});

console.log(overview.ndjson);
