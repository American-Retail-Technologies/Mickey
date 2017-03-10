using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;

namespace RmsInventoryImport
{
    // Reference: http://stackoverflow.com/questions/1405038/reading-a-csv-file-in-net
    public class CsvParser
    {
        private readonly Action<string> _handleErrorMessage;

        public CsvParser()
            : this(null)
        {
        }

        public CsvParser(Action<string> handleErrorMessage)
        {
            _handleErrorMessage = handleErrorMessage;
        }

        private Cell[] GetRecord(string line, TextReader sr, int rowNumber, int? expectedColumnCount)
        {
            var tokenedLine = Tokenize(line).ToArray();
            string nextLine;
            // while the 'row' data ends with an open quoted cell that contains a new line, gather the following line and append it
            int lastCellIndex;
            while (tokenedLine[lastCellIndex = tokenedLine.Length - 1].OpenQuote && ((nextLine = sr.ReadLine()) != null))
            {
                var tokenedNextLine = Tokenize(nextLine, quoteIsOpen: true).ToArray();

                var newLine = new Cell[lastCellIndex + tokenedNextLine.Length]; // yes it is one short

                tokenedLine[lastCellIndex].Content = tokenedLine[lastCellIndex].Content + tokenedNextLine[0].Content;

                tokenedLine.CopyTo(newLine, 0);
                for (int i = 1; i < tokenedNextLine.Length; i++) // first has been merged, skip it
                {
                    newLine[lastCellIndex + i] = tokenedNextLine[i];
                }

                tokenedLine = newLine;
            }

            if (expectedColumnCount.HasValue && tokenedLine.Length != expectedColumnCount.Value)
            {
                if (_handleErrorMessage != null)
                {
                    _handleErrorMessage(string.Format("Row {0} has {1} columns and is expecting {2}.", rowNumber, tokenedLine.Length, expectedColumnCount));
                }

                return null;
            }

            return tokenedLine;
        }

        public IEnumerable<string[]> GetCsvRecords(string csv, bool firstRowIsHeader)
        {
            if (string.IsNullOrEmpty(csv))
            {
                yield break;
            }

            // if it is delimited and ends with a newline, take the next line too
            using (var sr = new StringReader(csv))
            {
                int? expectedColumnCount = null;

                var rowNumber = 1;
                string line;
                while ((line = sr.ReadLine()) != null)
                {
                    var record = GetRecord(line, sr, rowNumber, expectedColumnCount);
                    if (record != null)
                    {
                        if (expectedColumnCount == null)
                        {
                            expectedColumnCount = record.Length;
                        }

                        if (rowNumber > 1 || !firstRowIsHeader)
                        {
                            yield return record.Select(x => x.Content).ToArray();
                        }
                    }

                    rowNumber++;
                }
            }
        }

        private IEnumerable<Cell> Tokenize(string line, bool quoteIsOpen = false)
        {
            var openQuote = quoteIsOpen;
            var itemChars = new List<char>();

            for (int index = 0; index < line.Length; index++)
            {
                var ch = line[index];

                if (ch == ',')
                {
                    if (!openQuote)
                    {
                        yield return new Cell { Content = new string(itemChars.ToArray()), OpenQuote = false };
                        itemChars = new List<char>();
                    }
                    else
                    {
                        itemChars.Add(ch);
                    }
                }
                else if (ch == '"')
                {
                    /*
                       If double-quotes are used to enclose fields, then a double-quote
                       appearing inside a field must be escaped by preceding it with
                       another double quote.  For example:
                       "aaa","b""bb","ccc"
                     */
                    // BUG: Does not take into account a case where the " is in the middle.
                    if (index == 0 || line[index - 1] == ',')
                    {
                        openQuote = true;
                    }
                    else if (openQuote)
                    {
                        if ((index != line.Length - 1) && line[index + 1] == '"') // not the last and the next is also a ", it is escaped
                        {
                            itemChars.Add(ch);
                            index++;
                        }
                        else
                        {
                            openQuote = false;
                        }
                    }
                    else
                    {
                        itemChars.Add(ch);
                    }
                }
                else
                {
                    itemChars.Add(ch);
                }
            }

            yield return new Cell { Content = new string(itemChars.ToArray()), OpenQuote = openQuote };
        }

        public class Cell
        {
            public string Content { get; set; }
            public bool OpenQuote { get; set; }
        }
    }
}
